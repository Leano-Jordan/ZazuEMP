<?php

namespace App\Http\Controllers;

use App\Services\BusinessArchiveEncryptionService;
use App\Services\BusinessArchiveService;
use App\Services\BusinessSpreadsheetExportService;
use App\Support\Audit;
use App\Support\CurrentBusiness;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BusinessExportController extends Controller
{
    public function index(
        Request $request,
        BusinessSpreadsheetExportService $exports,
        CurrentBusiness $currentBusiness,
    ): View {
        $business = $currentBusiness->model($request->user());

        return view('exports.index', [
            'business' => $business,
            'exports' => $exports->catalog($business),
        ]);
    }

    public function downloadArchive(
        Request $request,
        BusinessArchiveService $archives,
        BusinessArchiveEncryptionService $encryption,
        CurrentBusiness $currentBusiness,
    ): BinaryFileResponse|RedirectResponse {
        $validated = $request->validate([
            'archive_password' => ['required', 'string', 'min:12', 'max:128', 'confirmed'],
        ]);
        $business = $currentBusiness->model($request->user());
        $plainPath = $archives->download($business);

        try {
            $path = $encryption->encryptFile($plainPath, $validated['archive_password']);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['archive_password' => $exception->getMessage()]);
        } finally {
            @unlink($plainPath);
        }

        try {
            Audit::record('business.archive_exported', null, [
                'format' => 'zazu-business-archive-v2',
                'encryption' => 'xchacha20-poly1305',
                'kdf' => 'argon2id',
            ], (int) $business->id);
        } catch (\Throwable $exception) {
            @unlink($path);
            throw $exception;
        }

        return response()->download(
            $path,
            'zazu-business-'.Str::slug($business->name).'-'.now()->format('Ymd-His').'.zazu',
            [
                'Content-Type' => 'application/vnd.zazu.business-archive',
                'Cache-Control' => 'private, no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'X-Content-Type-Options' => 'nosniff',
            ]
        )->deleteFileAfterSend(true);
    }

    public function restoreArchive(
        Request $request,
        BusinessArchiveService $archives,
        BusinessArchiveEncryptionService $encryption,
        CurrentBusiness $currentBusiness,
    ): RedirectResponse {
        $business = $currentBusiness->model($request->user());

        try {
            $validated = $request->validate([
                'archive' => ['required', 'file', 'max:102400'],
                'archive_password' => ['nullable', 'string', 'min:12', 'max:128'],
            ]);
        } catch (ValidationException $exception) {
            return $this->rejectRestoreRequest($request, $business, $exception);
        }

        return $this->restoreValidatedArchive($request, $business, $validated, $archives, $encryption);
    }

    private function rejectRestoreRequest(
        Request $request,
        Business $business,
        ValidationException $exception,
    ): RedirectResponse {
        $incidentId = (string) Str::uuid();
        $uploadedFile = $request->file('archive');

        Log::warning('Business archive restore request rejected before processing.', [
            'incident_id' => $incidentId,
            'stage' => 'request_validation',
            'business_id' => (int) $business->id,
            'user_id' => (int) $request->user()->getAuthIdentifier(),
            'content_length' => (int) $request->server('CONTENT_LENGTH', 0),
            'upload_error' => $uploadedFile?->getError(),
            'upload_bytes' => $uploadedFile?->getSize(),
            'upload_max_filesize' => (string) ini_get('upload_max_filesize'),
            'post_max_size' => (string) ini_get('post_max_size'),
            'error_fields' => array_keys($exception->errors()),
        ]);

        $errors = $exception->errors();
        $archiveErrors = $errors['archive'] ?? [];
        $reference = 'Restore request reference: '.$incidentId;

        if ($archiveErrors === []) {
            $archiveErrors[] = $reference;
        } else {
            $archiveErrors[0] = rtrim($archiveErrors[0]).' '.$reference;
        }

        $errors['archive'] = $archiveErrors;

        return back()->withErrors($errors);
    }

    /**
     * @param array<string, mixed> $validated
     */
    private function restoreValidatedArchive(
        Request $request,
        Business $business,
        array $validated,
        BusinessArchiveService $archives,
        BusinessArchiveEncryptionService $encryption,
    ): RedirectResponse {
        $decryptedPath = null;
        $restoreStage = 'upload_access';

        try {
            $restorePath = $this->resolveRestoreArchivePath(
                $validated,
                $encryption,
                $restoreStage,
                $decryptedPath,
            );

            if ($restorePath === null) {
                return back()->withErrors(['archive_password' => 'Enter the passphrase used when this business archive was created.']);
            }

            $restoreStage = 'archive_validation_and_restore';
            $result = $archives->restore($restorePath, $business);
        } catch (RuntimeException $exception) {
            return $this->restoreRuntimeFailure(
                $request,
                $business,
                $validated['archive']->getSize(),
                $restoreStage,
                $exception,
            );
        } catch (\JsonException $exception) {
            return $this->restoreJsonFailure(
                $request,
                $business,
                $validated['archive']->getSize(),
                $restoreStage,
                $exception,
            );
        } finally {
            if (is_string($decryptedPath)) {
                @unlink($decryptedPath);
            }
        }

        Audit::record('business.archive_restored', null, [
            'tables' => $result['tables'],
            'records' => $result['records'],
            'files' => $result['files'],
            'encrypted' => is_string($decryptedPath),
        ], (int) $business->id);

        return redirect()->route('exports.index')->with(
            'success',
            sprintf('Business archive restored: %d records and %d files.', $result['records'], $result['files'])
        );
    }

    /**
     * @param array<string, mixed> $validated
     */
    private function resolveRestoreArchivePath(
        array $validated,
        BusinessArchiveEncryptionService $encryption,
        string &$restoreStage,
        ?string &$decryptedPath,
    ): ?string {
        $uploadedPath = $validated['archive']->getRealPath();
        $restorePath = is_string($uploadedPath) ? $uploadedPath : '';

        if ($restorePath === '' || ! is_file($restorePath) || ! is_readable($restorePath)) {
            throw new RuntimeException('The uploaded business archive could not be accessed by the server.');
        }

        $restoreStage = 'encryption_detection';
        if (! $encryption->isEncrypted($restorePath)) {
            return $restorePath;
        }

        if (! filled($validated['archive_password'] ?? null)) {
            return null;
        }

        $restoreStage = 'decryption';
        $decryptedPath = $encryption->decryptFile($restorePath, $validated['archive_password']);

        return $decryptedPath;
    }

    private function restoreRuntimeFailure(
        Request $request,
        Business $business,
        int $uploadBytes,
        string $restoreStage,
        RuntimeException $exception,
    ): RedirectResponse {
        $incidentId = (string) Str::uuid();
        Log::warning('Business archive restore failed.', [
            'incident_id' => $incidentId,
            'stage' => $restoreStage,
            'business_id' => (int) $business->id,
            'user_id' => (int) $request->user()->getAuthIdentifier(),
            'upload_bytes' => $uploadBytes,
            'exception_class' => $exception::class,
        ]);

        return back()->withErrors(['archive' => $exception->getMessage().' Reference: '.$incidentId]);
    }

    private function restoreJsonFailure(
        Request $request,
        Business $business,
        int $uploadBytes,
        string $restoreStage,
        \JsonException $exception,
    ): RedirectResponse {
        $incidentId = (string) Str::uuid();
        Log::warning('Business archive restore failed while decoding archive data.', [
            'incident_id' => $incidentId,
            'stage' => $restoreStage,
            'business_id' => (int) $business->id,
            'user_id' => (int) $request->user()->getAuthIdentifier(),
            'upload_bytes' => $uploadBytes,
            'exception_class' => $exception::class,
        ]);

        return back()->withErrors([
            'archive' => 'The uploaded archive is damaged or contains invalid data. Reference: '.$incidentId,
        ]);
    }

    public function download(
        Request $request,
        string $resource,
        BusinessSpreadsheetExportService $exports,
        CurrentBusiness $currentBusiness,
    ): StreamedResponse {
        $business = $currentBusiness->model($request->user());

        return $exports->download($business, $resource);
    }
}

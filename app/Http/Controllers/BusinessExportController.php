<?php

namespace App\Http\Controllers;

use App\Models\Business;
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
                'restore_as_new_workspace' => ['sometimes', 'boolean'],
            ]);
        } catch (ValidationException $exception) {
            return $this->rejectRestoreRequest($request, $business, $exception);
        }

        return $this->restoreValidatedArchive($request, $business, $validated, $archives, $encryption, $currentBusiness);
    }

    private function rejectRestoreRequest(
        Request $request,
        Business $business,
        ValidationException $exception,
    ): RedirectResponse {
        $incidentId = (string) Str::uuid();

        Log::warning('Business archive restore request rejected before processing.', [
            'incident_id' => $incidentId,
            'stage' => 'request_validation',
            'business_id' => (int) $business->id,
            'user_id' => (int) $request->user()->getAuthIdentifier(),
            ...$this->restoreDiagnostics($request),
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
     * @param  array<string, mixed>  $validated
     */
    private function restoreValidatedArchive(
        Request $request,
        Business $business,
        array $validated,
        BusinessArchiveService $archives,
        BusinessArchiveEncryptionService $encryption,
        CurrentBusiness $currentBusiness,
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
            $encrypted = is_string($decryptedPath);
            if ((bool) ($validated['restore_as_new_workspace'] ?? false)) {
                [$restoredBusiness, $result] = $this->restoreArchiveIntoNewWorkspace(
                    $request,
                    $restorePath,
                    $encrypted,
                    $archives,
                    $restoreStage,
                );
                $currentBusiness->switchTo((int) $restoredBusiness->id, $request->user());
            } else {
                $result = $this->restoreArchiveIntoBusiness(
                    $business,
                    $restorePath,
                    $encrypted,
                    $archives,
                    $restoreStage,
                );
            }
        } catch (RuntimeException $exception) {
            return $this->restoreRuntimeFailure(
                $request,
                $business,
                $restoreStage,
                $exception,
            );
        } catch (\JsonException $exception) {
            return $this->restoreJsonFailure(
                $request,
                $business,
                $restoreStage,
                $exception,
            );
        } catch (\Throwable $exception) {
            return $this->restoreUnexpectedFailure(
                $request,
                $business,
                $restoreStage,
                $exception,
            );
        } finally {
            if (is_string($decryptedPath)) {
                @unlink($decryptedPath);
            }
        }

        return redirect()->route('exports.index')->with(
            'success',
            sprintf('Business archive restored: %d records and %d files.', $result['records'], $result['files'])
        );
    }

    /**
     * @return array{tables:int,records:int,files:int}
     */
    private function restoreArchiveIntoBusiness(
        Business $business,
        string $restorePath,
        bool $encrypted,
        BusinessArchiveService $archives,
        string &$restoreStage,
    ): array {
        return $archives->restore(
            $restorePath,
            $business,
            static function (array $result) use ($business, $encrypted): void {
                Audit::record('business.archive_restored', null, [
                    'tables' => $result['tables'],
                    'records' => $result['records'],
                    'files' => $result['files'],
                    'encrypted' => $encrypted,
                ], (int) $business->id);
            },
            static function (string $stage) use (&$restoreStage): void {
                $restoreStage = $stage;
            }
        );
    }

    /**
     * Create a separate owner workspace without disturbing the currently selected business.
     *
     * @return array{0:Business,1:array{tables:int,records:int,files:int}}
     */
    private function restoreArchiveIntoNewWorkspace(
        Request $request,
        string $restorePath,
        bool $encrypted,
        BusinessArchiveService $archives,
        string &$restoreStage,
    ): array {
        $user = $request->user();
        $business = \Illuminate\Support\Facades\DB::transaction(function () use ($user): Business {
            $business = Business::create([
                'name' => 'Restoring business archive',
                'slug' => 'restored-'.Str::lower(Str::random(12)),
                'status' => 'active',
                'currency' => 'ZAR',
            ]);
            $business->users()->attach($user->getAuthIdentifier(), ['role' => 'owner']);

            return $business;
        });

        try {
            $result = $this->restoreArchiveIntoBusiness(
                $business,
                $restorePath,
                $encrypted,
                $archives,
                $restoreStage,
            );
        } catch (\Throwable $exception) {
            try {
                \Illuminate\Support\Facades\DB::transaction(
                    static fn () => Business::query()->whereKey($business->id)->delete()
                );
            } catch (\Throwable $cleanupException) {
                Log::critical('Failed business archive restore left an empty destination workspace.', [
                    'business_id' => (int) $business->id,
                    'exception_class' => $cleanupException::class,
                ]);
            }

            throw $exception;
        }

        return [$business, $result];
    }

    /**
     * @param  array<string, mixed>  $validated
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
        string $restoreStage,
        RuntimeException $exception,
    ): RedirectResponse {
        $incidentId = (string) Str::uuid();
        Log::warning('Business archive restore failed.', [
            'incident_id' => $incidentId,
            'stage' => $restoreStage,
            'business_id' => (int) $business->id,
            'user_id' => (int) $request->user()->getAuthIdentifier(),
            ...$this->restoreDiagnostics($request),
            'exception_class' => $exception::class,
            'exception_code' => (string) $exception->getCode(),
        ]);

        return back()->withErrors(['archive' => $exception->getMessage().' Reference: '.$incidentId]);
    }

    private function restoreUnexpectedFailure(
        Request $request,
        Business $business,
        string $restoreStage,
        \Throwable $exception,
    ): RedirectResponse {
        $incidentId = (string) Str::uuid();
        Log::error('Business archive restore failed unexpectedly.', [
            'incident_id' => $incidentId,
            'stage' => $restoreStage,
            'business_id' => (int) $business->id,
            'user_id' => (int) $request->user()->getAuthIdentifier(),
            ...$this->restoreDiagnostics($request),
            'exception_class' => $exception::class,
            'exception_code' => (string) $exception->getCode(),
        ]);

        return back()->withErrors([
            'archive' => 'Zazu could not complete this business archive restore. No imported workspace should be treated as restored. Reference: '.$incidentId,
        ]);
    }

    private function restoreJsonFailure(
        Request $request,
        Business $business,
        string $restoreStage,
        \JsonException $exception,
    ): RedirectResponse {
        $incidentId = (string) Str::uuid();
        Log::warning('Business archive restore failed while decoding archive data.', [
            'incident_id' => $incidentId,
            'stage' => $restoreStage,
            'business_id' => (int) $business->id,
            'user_id' => (int) $request->user()->getAuthIdentifier(),
            ...$this->restoreDiagnostics($request),
            'exception_class' => $exception::class,
        ]);

        return back()->withErrors([
            'archive' => 'The uploaded archive is damaged or contains invalid data. Reference: '.$incidentId,
        ]);
    }

    /**
     * Safe runtime context for diagnosing archive restore failures.
     * Never records the passphrase, uploaded path, archive contents or exception message.
     *
     * @return array<string,int|string|bool|null>
     */
    private function restoreDiagnostics(Request $request): array
    {
        $uploadedFile = $request->file('archive');
        $temporaryPath = $uploadedFile?->getRealPath();
        $temporaryDirectory = is_string($temporaryPath) && $temporaryPath !== '' ? dirname($temporaryPath) : sys_get_temp_dir();
        $freeBytes = @disk_free_space($temporaryDirectory);
        $uploadBytes = $uploadedFile?->getSize();

        return [
            'content_length' => (int) $request->server('CONTENT_LENGTH', 0),
            'upload_error' => $uploadedFile?->getError(),
            'upload_bytes' => is_int($uploadBytes) ? $uploadBytes : null,
            'upload_file_readable' => is_string($temporaryPath) && is_readable($temporaryPath),
            'upload_max_filesize' => (string) ini_get('upload_max_filesize'),
            'post_max_size' => (string) ini_get('post_max_size'),
            'memory_limit' => (string) ini_get('memory_limit'),
            'max_execution_time' => (string) ini_get('max_execution_time'),
            'max_input_time' => (string) ini_get('max_input_time'),
            'memory_usage_bytes' => memory_get_usage(true),
            'peak_memory_usage_bytes' => memory_get_peak_usage(true),
            'temporary_directory_writable' => is_writable($temporaryDirectory),
            'temporary_directory_free_bytes' => is_int($freeBytes) || is_float($freeBytes) ? (int) $freeBytes : null,
            'zip_extension_loaded' => extension_loaded('zip'),
            'sodium_extension_loaded' => extension_loaded('sodium'),
        ];
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

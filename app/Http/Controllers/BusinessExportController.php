<?php

namespace App\Http\Controllers;

use App\Services\BusinessSpreadsheetExportService;
use App\Services\BusinessArchiveService;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use RuntimeException;
use App\Support\CurrentBusiness;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
        CurrentBusiness $currentBusiness,
    ): BinaryFileResponse {
        $business = $currentBusiness->model($request->user());
        $path = $archives->download($business);

        Audit::record('business.archive_exported', null, ['format' => 'zazu-business-archive-v1'], (int) $business->id);

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
        CurrentBusiness $currentBusiness,
    ): RedirectResponse {
        $validated = $request->validate([
            'archive' => ['required', 'file', 'max:102400'],
        ]);

        $business = $currentBusiness->model($request->user());

        try {
            $result = $archives->restore($validated['archive']->getRealPath(), $business);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['archive' => $exception->getMessage()]);
        }

        Audit::record('business.archive_restored', null, [
            'tables' => $result['tables'],
            'records' => $result['records'],
            'files' => $result['files'],
        ], (int) $business->id);

        return redirect()->route('exports.index')->with(
            'success',
            sprintf('Business archive restored: %d records and %d files.', $result['records'], $result['files'])
        );
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

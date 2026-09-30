<?php

namespace App\Http\Controllers;

use App\Services\CustomerImportMapper;
use App\Services\CustomerImportService;
use App\Services\SpreadsheetReader;
use App\Support\CurrentBusiness;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;
use RuntimeException;

class CustomerImportController extends Controller
{
    public function create(): View
    {
        return view('customers.import');
    }

    public function preview(
        Request $request,
        SpreadsheetReader $reader,
        CustomerImportMapper $mapper,
    ): View {
        $business = app(CurrentBusiness::class)->model($request->user());

        $validated = $request->validate([
            'spreadsheet' => [
                'required',
                File::types(['csv', 'xlsx'])->max(5120),
            ],
        ]);

        $file = $validated['spreadsheet'];
        $rows = $reader->read(
            $file->getRealPath(),
            $file->getClientOriginalName(),
            $file->getMimeType(),
        )['rows'];

        if ($rows === []) {
            throw new RuntimeException('The spreadsheet contains no data rows.');
        }

        if (count($rows) > 5000) {
            throw new RuntimeException('The spreadsheet is too large for an interactive import. Use 5,000 rows or fewer.');
        }

        $request->session()->put('customer_import', [
            'business_id' => $business->id,
            'rows' => $rows,
        ]);

        $preview = $mapper->preview($rows, $business);

        return view('customers.import', compact('preview'));
    }

    public function import(
        Request $request,
        CustomerImportService $importer,
    ): RedirectResponse {
        $business = app(CurrentBusiness::class)->model($request->user());
        $import = $request->session()->get('customer_import');
        $rows = is_array($import) ? ($import['rows'] ?? null) : null;

        if (!is_array($rows) || $rows === []) {
            return redirect()
                ->route('customers.import.create')
                ->withErrors(['spreadsheet' => 'The import preview has expired. Upload the spreadsheet again.']);
        }

        if ((int) ($import['business_id'] ?? 0) !== (int) $business->id) {
            $request->session()->forget('customer_import');

            return redirect()
                ->route('customers.import.create')
                ->withErrors(['spreadsheet' => 'The import belongs to a different business workspace. Upload it again.']);
        }

        $actions = $request->input('actions', []);

        if (!is_array($actions)) {
            $actions = [];
        }

        $result = $importer->import($business, $rows, $actions);

        $request->session()->forget('customer_import');

        return redirect()
            ->route('customers.index')
            ->with('success', "{$result['created']} customer(s) imported; {$result['skipped']} existing customer(s) skipped.");
    }
}

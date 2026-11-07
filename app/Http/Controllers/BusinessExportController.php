<?php

namespace App\Http\Controllers;

use App\Services\BusinessSpreadsheetExportService;
use App\Support\CurrentBusiness;
use Illuminate\Http\Request;
use Illuminate\View\View;
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

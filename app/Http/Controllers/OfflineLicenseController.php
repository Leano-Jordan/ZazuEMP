<?php

namespace App\\Http\\Controllers;

use App\\Services\\OfflineLicenseService;
use App\\Support\\Audit;
use App\\Support\\CurrentBusiness;
use Illuminate\\Http\\RedirectResponse;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Support\\Facades\\Validator;

class OfflineLicenseController extends Controller
{
    public function store(Request $request, OfflineLicenseService $licenses): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'license_token' => ['required', 'string', 'max:16384'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('settings.index')
                ->withErrors($validator)
                ->withInput($request->except('license_token'));
        }

        $business = app(CurrentBusiness::class)->model($request->user());

        try {
            DB::transaction(function () use ($business, $request, $licenses): void {
                $license = $licenses->importSignedLicense(
                    $business,
                    trim((string) $request->input('license_token')),
                );

                Audit::record('business.license.imported', $license, [
                    'license_id' => $license->license_id,
                    'plan' => $license->plan,
                    'status' => $license->status,
                    'expires_at' => $license->expires_at?->toIso8601String(),
                ], (int) $business->id);
            });
        } catch (\\InvalidArgumentException $exception) {
            return redirect()
                ->route('settings.index')
                ->withErrors(['license_token' => $exception->getMessage()]);
        }

        return redirect()
            ->route('settings.index')
            ->with('success', 'The signed Zazu licence was verified and installed for this business.');
    }
}

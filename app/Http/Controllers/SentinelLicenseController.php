<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

final class SentinelLicenseController extends Controller
{
    public function issue(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'business_id' => ['required', 'integer', 'min:1', 'exists:businesses,id'],
            'plan' => ['required', 'in:basic,intermediate,advanced'],
            'expires_at' => ['nullable', 'date_format:Y-m-d\\TH:i'],
            'never_expires' => ['nullable', 'boolean'],
        ]);

        $neverExpires = (bool) ($validated['never_expires'] ?? false);
        if ($neverExpires === filled($validated['expires_at'] ?? null)) {
            return back()->withErrors([
                'expires_at' => 'Choose an expiry date or explicitly select never expires.',
            ])->withInput();
        }

        $business = Business::query()->with('license')->findOrFail($validated['business_id']);
        $keyFile = trim((string) config('zazu.licensing.private_key_file', ''));

        if ($keyFile === '') {
            return back()->with('error', 'Licence signing is not configured on this installation. Configure the external signing-key file before issuing licences.');
        }

        $arguments = [
            'business_id' => (string) $business->id,
            'plan' => $validated['plan'],
            '--private-key-file' => $keyFile,
            '--starts-at' => now('UTC')->format('Y-m-d\\TH:i:s\\Z'),
            '--features' => '{}',
        ];

        if ($business->license?->license_id) {
            $arguments['--license-id'] = $business->license->license_id;
        }

        if ($neverExpires) {
            $arguments['--never-expires'] = true;
        } else {
            $arguments['--expires-at'] = now(config('app.timezone'))
                ->parse($validated['expires_at'])
                ->seconds(0)
                ->toIso8601String();
        }

        return $this->runIssuer($arguments, $business, 'issued', $validated['plan']);
    }

    public function revoke(Request $request, Business $business): RedirectResponse
    {
        $business->load('license');
        abort_unless($business->license?->license_id, 404);

        $keyFile = trim((string) config('zazu.licensing.private_key_file', ''));
        if ($keyFile === '') {
            return back()->with('error', 'Licence signing is not configured on this installation.');
        }

        return $this->runIssuer([
            'business_id' => (string) $business->id,
            'plan' => $business->license->plan,
            '--private-key-file' => $keyFile,
            '--license-id' => $business->license->license_id,
            '--status' => 'revoked',
            '--starts-at' => now('UTC')->format('Y-m-d\\TH:i:s\\Z'),
            '--never-expires' => true,
            '--features' => '{}',
        ], $business, 'revoked', $business->license->plan);
    }

    private function runIssuer(array $arguments, Business $business, string $action, string $plan): RedirectResponse
    {
        try {
            $exitCode = Artisan::call('zazu:license:issue', $arguments);
            $output = Artisan::output();
        } catch (\\Throwable $exception) {
            Log::warning('Sentinel licence issuer failed', [
                'business_id' => $business->id,
                'action' => $action,
                'failure_class' => class_basename($exception),
            ]);

            return back()->with('error', 'Zazu could not create the signed licence. Check the founder signing configuration.');
        }

        if ($exitCode !== 0
            || ! preg_match('/^TOKEN: ([A-Za-z0-9_-]+\\.[A-Za-z0-9_-]+)$/m', $output, $tokenMatch)
            || ! preg_match('/^Licence ID: ([0-9a-f-]{36})$/mi', $output, $idMatch)) {
            Log::warning('Sentinel licence issuance was rejected', [
                'business_id' => $business->id,
                'action' => $action,
                'issuer_exit_code' => $exitCode,
            ]);

            return back()->with('error', 'Zazu could not issue this licence. Check the signing-key file and public-key match.');
        }

        Log::notice('Sentinel founder licence '.$action, [
            'business_id' => $business->id,
            'business_name' => $business->name,
            'license_id' => strtolower($idMatch[1]),
            'plan' => $plan,
            'action' => $action,
            'actor_user_id' => auth()->id(),
            'expires_at' => $arguments['--expires-at'] ?? null,
        ]);

        session()->flash('sentinel_issued_license_token', $tokenMatch[1]);
        session()->flash('sentinel_issued_license_business', $business->name);
        session()->flash('sentinel_issued_license_action', $action);

        return redirect()->route('admin.sentinel', ['section' => 'licensing']);
    }
}

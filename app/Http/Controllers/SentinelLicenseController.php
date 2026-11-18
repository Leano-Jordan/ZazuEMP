<?php

namespace App\Http\Controllers;

use App\Models\PlatformLicenseCustomer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

final class SentinelLicenseController extends Controller
{
    public function register(Request $request): RedirectResponse
    {
        $request->merge(['business_uuid' => strtolower(trim((string) $request->input('business_uuid')))]);
        $validated = $request->validate([
            'business_uuid' => ['required', 'uuid', 'unique:platform_license_customers,business_uuid'],
            'business_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        PlatformLicenseCustomer::query()->create($validated + [
            'created_by' => auth()->id(),
        ]);

        Log::notice('Sentinel founder registered a licensing customer', [
            'business_uuid' => strtolower($validated['business_uuid']),
            'business_name' => $validated['business_name'],
            'actor_user_id' => auth()->id(),
        ]);

        return redirect()->route('admin.sentinel', ['section' => 'licensing']);
    }

    public function issue(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'platform_license_customer_id' => ['required', 'integer', 'min:1', 'exists:platform_license_customers,id'],
            'plan' => ['required', 'in:basic,intermediate,advanced'],
            'expires_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'never_expires' => ['nullable', 'boolean'],
        ]);

        $neverExpires = (bool) ($validated['never_expires'] ?? false);
        if ($neverExpires === filled($validated['expires_at'] ?? null)) {
            return back()->withErrors([
                'expires_at' => 'Choose an expiry date or explicitly select never expires.',
            ])->withInput();
        }

        $customer = PlatformLicenseCustomer::query()->findOrFail($validated['platform_license_customer_id']);
        $keyFile = trim((string) config('zazu.licensing.private_key_file', ''));

        if ($keyFile === '') {
            return back()->with('error', 'Licence signing is not configured on this installation. Configure the external signing-key file before issuing licences.');
        }

        $arguments = [
            'business_uuid' => $customer->business_uuid,
            'plan' => $validated['plan'],
            '--private-key-file' => $keyFile,
            '--starts-at' => now('UTC')->format('Y-m-d\TH:i:s\Z'),
            '--features' => '{}',
        ];

        if ($customer->last_license_id) {
            $arguments['--license-id'] = $customer->last_license_id;
        }

        if ($neverExpires) {
            $arguments['--never-expires'] = true;
        } else {
            $arguments['--expires-at'] = \Illuminate\Support\Carbon::parse($validated['expires_at'], config('app.timezone'))
                ->seconds(0)
                ->toIso8601String();
        }

        return $this->runIssuer($arguments, $customer, 'issued', $validated['plan']);
    }

    public function revoke(PlatformLicenseCustomer $customer): RedirectResponse
    {
        abort_unless($customer->last_license_id && $customer->last_plan, 404);

        $keyFile = trim((string) config('zazu.licensing.private_key_file', ''));
        if ($keyFile === '') {
            return back()->with('error', 'Licence signing is not configured on this installation.');
        }

        return $this->runIssuer([
            'business_uuid' => $customer->business_uuid,
            'plan' => $customer->last_plan,
            '--private-key-file' => $keyFile,
            '--license-id' => $customer->last_license_id,
            '--status' => 'revoked',
            '--starts-at' => now('UTC')->format('Y-m-d\TH:i:s\Z'),
            '--never-expires' => true,
            '--features' => '{}',
        ], $customer, 'revoked', $customer->last_plan);
    }

    private function runIssuer(
        array $arguments,
        PlatformLicenseCustomer $customer,
        string $action,
        string $plan,
    ): RedirectResponse {
        try {
            $exitCode = Artisan::call('zazu:license:issue', $arguments);
            $output = Artisan::output();
        } catch (\Throwable $exception) {
            Log::warning('Sentinel licence issuer failed', [
                'business_uuid' => $customer->business_uuid,
                'action' => $action,
                'failure_class' => class_basename($exception),
            ]);

            return back()->with('error', 'Zazu could not create the signed licence. Check the founder signing configuration.');
        }

        if ($exitCode !== 0
            || ! preg_match('/^TOKEN: ([A-Za-z0-9_-]+\.[A-Za-z0-9_-]+)$/m', $output, $tokenMatch)
            || ! preg_match('/^Licence ID: ([0-9a-f-]{36})$/mi', $output, $idMatch)
            || ! preg_match('/^Issued at: (.+)$/mi', $output, $issuedAtMatch)) {
            Log::warning('Sentinel licence issuance was rejected', [
                'business_uuid' => $customer->business_uuid,
                'action' => $action,
                'issuer_exit_code' => $exitCode,
            ]);

            return back()->with('error', 'Zazu could not issue this licence. Check the signing-key file and public-key match.');
        }

        $customer->forceFill([
            'last_license_id' => strtolower($idMatch[1]),
            'last_plan' => $plan,
            'last_status' => $action === 'revoked' ? 'revoked' : 'active',
            'last_starts_at' => \\Illuminate\\Support\\Carbon::parse($arguments['--starts-at'] ?? now('UTC')),
            'last_expires_at' => isset($arguments['--expires-at']) ? \\Illuminate\\Support\\Carbon::parse($arguments['--expires-at']) : null,
            'last_issued_at' => \Illuminate\Support\Carbon::parse(trim($issuedAtMatch[1])),
        ])->save();

        Log::notice('Sentinel founder licence '.$action, [
            'business_uuid' => $customer->business_uuid,
            'business_name' => $customer->business_name,
            'license_id' => strtolower($idMatch[1]),
            'plan' => $plan,
            'action' => $action,
            'actor_user_id' => auth()->id(),
            'expires_at' => $arguments['--expires-at'] ?? null,
        ]);

        session()->flash('sentinel_issued_license_token', $tokenMatch[1]);
        session()->flash('sentinel_issued_license_business', $customer->business_name);
        session()->flash('sentinel_issued_license_action', $action);

        return redirect()->route('admin.sentinel', ['section' => 'licensing']);
    }
}

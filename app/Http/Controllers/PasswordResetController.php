<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Business;
use App\Models\SyncDevice;
use App\Models\User;
use App\Support\Audit;
use App\Support\CurrentBusiness;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function request(): View
    {
        return view('auth.forgot-password');
    }

    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
        ]);

        $identifier = Str::lower(trim($validated['identifier']));

        $user = User::query()
            ->where(function ($query) use ($identifier) {
                $query->where('username', $identifier)
                    ->orWhere('email', $identifier);
            })
            ->first();

        Password::sendResetLink([
            'email' => $user?->email ?? ('unknown+'.Str::uuid().'@invalid.local'),
        ]);

        return back()->with(
            'status',
            'If an account matches those details, we sent a password reset link to its email address.'
        );
    }

    public function edit(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => Str::lower(trim($request->string('email')->toString())),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower(trim($request->string('email')->toString())),
        ]);

        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:10', 'confirmed'],
        ]);

        $status = Password::reset(
            $validated,
            function (User $user, string $password): void {
                DB::transaction(function () use ($user, $password): void {
                    $user->password = $password;
                    $user->setRememberToken(Str::random(60));
                    $user->save();

                    event(new PasswordReset($user));
                    $this->revokeOwnedBusinessDevices($user);
                });
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'That password reset link is invalid or has expired. Request a new one and try again.',
                ]);
        }

        $user = User::query()->where('email', $validated['email'])->firstOrFail();

        Auth::login($user);
        $request->session()->regenerate();

        // Revoke existing database-backed sessions immediately. For other
        // drivers, AuthenticateSession + logoutOtherDevices provide the guard.
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))
                ->where('user_id', $user->id)
                ->where('id', '!=', $request->session()->getId())
                ->delete();
        }

        Auth::logoutOtherDevices($validated['password']);

        $ownedBusiness = $user->businesses()
            ->where('businesses.status', 'active')
            ->wherePivot('role', 'owner')
            ->orderBy('businesses.id')
            ->first();

        if ($ownedBusiness) {
            $request->session()->put(CurrentBusiness::SESSION_KEY, $ownedBusiness->id);
        } else {
            app(CurrentBusiness::class)->resolve($user);
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Your password has been reset.');
    }

    /**
     * Trusted sync credentials belong to a business rather than a single user.
     * Revoking devices across the reset owner's businesses ensures stale bearer
     * tokens cannot survive account recovery.
     */
    private function revokeOwnedBusinessDevices(User $user): void
    {
        $businessIds = $user->businesses()
            ->wherePivot('role', 'owner')
            ->pluck('businesses.id');

        if ($businessIds->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($businessIds): void {
            $businesses = Business::query()
                ->whereIn('id', $businessIds)
                ->get()
                ->keyBy('id');

            SyncDevice::query()
                ->whereIn('business_id', $businessIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->each(fn (SyncDevice $device) => $this->revokeTrustedDevice($device, $businesses));
        });
    }

    /**
     * @param \Illuminate\Support\Collection<int, Business> $businesses
     */
    private function revokeTrustedDevice(SyncDevice $device, \Illuminate\Support\Collection $businesses): void
    {
        $metadata = $device->metadata ?? [];
        $needsAudit = $device->status !== 'revoked' || $device->revoked_at === null;

        if (! $needsAudit && ! $this->deviceHasRevocableCredentials($device, $metadata)) {
            return;
        }

        $this->persistTrustedDeviceRevocation($device, $metadata);
        $this->recordTrustedDeviceRevocationAudit($device, $businesses, $needsAudit);
    }

    private function deviceHasRevocableCredentials(SyncDevice $device, array $metadata): bool
    {
        return $device->token_expires_at !== null
            || array_key_exists('sync_token_hash', $metadata)
            || array_key_exists('pairing_code_hash', $metadata)
            || array_key_exists('pairing_expires_at', $metadata)
            || $device->pairing_code_digest !== null;
    }

    private function persistTrustedDeviceRevocation(SyncDevice $device, array $metadata): void
    {
        unset($metadata['sync_token_hash'], $metadata['pairing_code_hash'], $metadata['pairing_expires_at']);

        // Recovery intentionally spans every business this user owns. The
        // normal BelongsToBusiness saving guard protects the currently selected
        // workspace; this owner-scoped recovery has already established each
        // target business through the user's owner pivot, so persist quietly.
        $device->forceFill([
            'status' => 'revoked',
            'revoked_at' => $device->revoked_at ?? now(),
            'token_expires_at' => null,
            'pairing_code_digest' => null,
            'metadata' => $metadata,
        ])->saveQuietly();
    }

    /**
     * @param \Illuminate\Support\Collection<int, Business> $businesses
     */
    private function recordTrustedDeviceRevocationAudit(
        SyncDevice $device,
        \Illuminate\Support\Collection $businesses,
        bool $needsAudit,
    ): void {
        if (! $needsAudit) {
            return;
        }

        $business = $businesses->get($device->business_id);
        if (! $business instanceof Business) {
            return;
        }

        // The audit belongs to the target business, which may not be the
        // currently selected workspace during multi-business account recovery.
        AuditLog::withoutEvents(fn () => Audit::record('sync.device.revoked', $business, [
            'device_id' => (int) $device->id,
            'device_name' => $device->device_name,
            'device_type' => $device->device_type,
            'reason' => 'owner_password_reset',
        ], $business->id));
    }

}

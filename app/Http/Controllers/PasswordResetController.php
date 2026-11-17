<?php

namespace App\Http\Controllers;

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
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $validated,
            function (User $user, string $password): void {
                $user->password = $password;
                $user->setRememberToken(Str::random(60));
                $user->save();

                event(new PasswordReset($user));
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

        // Sync devices are business-scoped rather than user-scoped. After an
        // owner resets their password, revoke every trusted device in each
        // business they own so old bearer tokens cannot outlive account recovery.
        $ownedBusinessIds = $user->businesses()
            ->wherePivot('role', 'owner')
            ->pluck('businesses.id');

        if ($ownedBusinessIds->isNotEmpty()) {
            DB::transaction(function () use ($ownedBusinessIds): void {
                $businesses = Business::query()
                    ->whereIn('id', $ownedBusinessIds)
                    ->get()
                    ->keyBy('id');

                $devices = SyncDevice::query()
                    ->whereIn('business_id', $ownedBusinessIds)
                    ->lockForUpdate()
                    ->get();

                foreach ($devices as $device) {
                    $metadata = $device->metadata ?? [];
                    $needsAudit = $device->status !== 'revoked' || $device->revoked_at === null;
                    $needsCleanup = $device->token_expires_at !== null
                        || array_key_exists('sync_token_hash', $metadata)
                        || array_key_exists('pairing_code_hash', $metadata)
                        || array_key_exists('pairing_expires_at', $metadata);

                    if (! $needsAudit && ! $needsCleanup) {
                        continue;
                    }

                    unset($metadata['sync_token_hash'], $metadata['pairing_code_hash'], $metadata['pairing_expires_at']);

                    $device->forceFill([
                        'status' => 'revoked',
                        'revoked_at' => $device->revoked_at ?? now(),
                        'token_expires_at' => null,
                        'metadata' => $metadata,
                    ])->save();

                    if ($needsAudit && isset($businesses[$device->business_id])) {
                        $business = $businesses[$device->business_id];
                        Audit::record('sync.device.revoked', $business, [
                            'device_id' => (int) $device->id,
                            'device_name' => $device->device_name,
                            'device_type' => $device->device_type,
                            'reason' => 'owner_password_reset',
                        ], $business->id);
                    }
                }
            });
        }

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
}

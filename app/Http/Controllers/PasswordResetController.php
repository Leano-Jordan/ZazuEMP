<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\CurrentBusiness;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        if ($user) {
            Password::sendResetLink([
                'email' => $user->email,
            ]);
        }

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
        app(CurrentBusiness::class)->resolve($user);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Your password has been reset.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\RegisterBusiness;
use App\Support\CurrentBusiness;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.login', [
            'ownerAccess' => $request->boolean('owner') || $request->routeIs('owner.login'),
        ]);
    }

    public function register(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $identifier = Str::lower(trim($credentials['identifier']));

        $user = User::query()
            ->where(function ($query) use ($identifier) {
                $query->where('username', $identifier)
                    ->orWhere('email', $identifier);
            })
            ->first();

        if (!$user || !Auth::attempt([
            'id' => $user->id,
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'identifier' => 'We could not sign you in with those details.',
            ]);
        }

        $request->session()->regenerate();

        app(CurrentBusiness::class)->resolve($request->user());

        if ($request->boolean('owner_access')) {
            $business = $request->user()->businesses()
                ->where('businesses.status', 'active')
                ->wherePivot('role', 'owner')
                ->orderBy('businesses.id')
                ->first();

            if (!$business) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'identifier' => 'This account does not have owner access to an active workspace.',
                ]);
            }

            $request->session()->put(CurrentBusiness::SESSION_KEY, $business->id);

            return redirect()->route('owner.dashboard');
        }

        return redirect()->intended(route('dashboard'));
    }

    public function storeRegistration(Request $request, RegisterBusiness $registration): RedirectResponse
    {
        $request->merge([
            'username' => Str::lower(trim($request->string('username')->toString())),
            'email' => Str::lower(trim($request->string('email')->toString())),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:32',
                'regex:/^[a-z0-9][a-z0-9._-]{2,31}$/',
                'unique:users,username',
            ],
            'business_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $registration->register($validated);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function owner(): View
    {
        $business = app(CurrentBusiness::class)->model(request()->user());
        $ownerCount = $business->users()->wherePivot('role', 'owner')->count();
        $staffCount = $business->users()->wherePivot('role', 'staff')->count();
        $customerCount = $business->customers()->count();
        $jobCount = $business->events()->whereNotIn('status', ['completed', 'cancelled'])->count();
        $capabilityCount = $business->capabilities()->where('is_active', true)->count();

        return view('owner.index', compact(
            'business',
            'ownerCount',
            'staffCount',
            'customerCount',
            'jobCount',
            'capabilityCount'
        ));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

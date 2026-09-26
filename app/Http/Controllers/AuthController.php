<?php

namespace App\Http\Controllers;

use App\Services\RegisterBusiness;
use App\Support\CurrentBusiness;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.login', [
            'ownerAccess' => $request->boolean('owner'),
        ]);
    }

    public function register(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Those sign-in details could not be verified.',
            ]);
        }

        $request->session()->regenerate();
        app(CurrentBusiness::class)->resolve($request->user());

        if ($request->boolean('owner_access')) {
            $business = app(CurrentBusiness::class)->resolve($request->user());
            $isOwner = $business
                && $request->user()->businesses()
                    ->whereKey($business->id)
                    ->wherePivot('role', 'owner')
                    ->exists();

            if (!$isOwner) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'email' => 'This account does not have owner access to the active workspace.',
                ]);
            }

            return redirect()->route('owner.dashboard');
        }

        return redirect()->intended(route('dashboard'));
    }

    public function storeRegistration(Request $request, RegisterBusiness $registration): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
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

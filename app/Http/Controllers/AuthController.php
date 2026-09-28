<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\User;
use App\Services\RegisterBusiness;
use App\Support\CurrentBusiness;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

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

    public function workspaceRecovery(): View
    {
        return view('auth.workspace-recovery');
    }

    public function storeWorkspaceRecovery(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user, 403);

        if (app(CurrentBusiness::class)->resolve($user)) {
            return redirect()->route('dashboard');
        }

        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
        ]);

        $business = \Illuminate\Support\Facades\DB::transaction(function () use ($user, $validated): Business {
            $business = Business::create([
                'name' => trim($validated['business_name']),
                'slug' => Str::slug($validated['business_name']).'-'.Str::lower(Str::random(8)),
                'status' => 'active',
                'currency' => 'ZAR',
            ]);

            $business->users()->attach($user->id, ['role' => 'owner']);

            return $business;
        });

        $request->session()->put(CurrentBusiness::SESSION_KEY, $business->id);

        return redirect()->route('onboarding.catalogue');
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
                function ($attribute, $value, $fail): void {
                    if (User::query()->whereRaw('LOWER(email) = ?', [$value])->exists()) {
                        $fail('Choose a username that is different from every account email address.');
                    }
                },
            ],
            'business_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
                function ($attribute, $value, $fail): void {
                    if (User::query()->where('username', Str::lower(trim($value)))->exists()) {
                        $fail('Use an email address that is different from every username.');
                    }
                },
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Schema::hasColumn('users', 'username')
            || !Schema::hasColumn('businesses', 'currency')
            || !Schema::hasTable('business_user')) {
            throw ValidationException::withMessages([
                'business_name' => 'Zazu cannot create the workspace because the local database is not up to date. Run the latest migrations, then try again.',
            ]);
        }

        try {
            $user = $registration->register($validated);
        } catch (Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'business_name' => 'We could not create this workspace. No account was activated. Check that the database migrations are current and try again.',
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('onboarding.catalogue');
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

        return redirect()->route('landing');
    }
}

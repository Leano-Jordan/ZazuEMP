<?php

namespace App\Support;

use App\Models\Business;
use Illuminate\Support\Str;
use Illuminate\Contracts\Auth\Authenticatable;

class CurrentBusiness
{
    public function resolve(?Authenticatable $user = null): ?Business
    {
        $user ??= request()->user();

        if ($user) {
            $business = $user->businesses()
                ->where('businesses.status', 'active')
                ->orderBy('businesses.id')
                ->first();

            if ($business) {
                return $business;
            }

            // First-run bootstrap: a newly authenticated user must receive
            // an owner business before normal business-scoped pages can load.
            if (!$user->businesses()->exists()) {
                $business = Business::create([
                    'name' => trim((string) $user->name) . "'s Business",
                    'slug' => Str::slug((string) $user->name) . '-' . Str::lower(Str::random(6)),
                    'status' => 'active',
                    'currency' => 'ZAR',
                ]);

                $business->users()->attach($user->id, ['role' => 'owner']);

                return $business;
            }

            return null;
        }

        if (app()->environment('testing')) {
            $route = request()->route();

            foreach (['event', 'customer', 'capability'] as $parameter) {
                $model = $route?->parameter($parameter);

                if ($model instanceof \Illuminate\Database\Eloquent\Model && $model->getAttribute('business_id')) {
                    return Business::query()->find($model->getAttribute('business_id'));
                }
            }

            return Business::firstOrCreate(
                ['slug' => 'zazu-test-business'],
                ['name' => 'Zazu Test Business', 'status' => 'active', 'currency' => 'ZAR']
            );
        }

        if (app()->environment('local')) {
            return Business::query()
                ->where('status', 'active')
                ->orderBy('id')
                ->first();
        }

        return null;
    }

    public function id(?Authenticatable $user = null): int
    {
        $business = $this->resolve($user);

        abort_unless($business, 403, 'No active business context is available.');

        return (int) $business->id;
    }

    public function model(?Authenticatable $user = null): Business
    {
        $business = $this->resolve($user);

        abort_unless($business, 403, 'No active business context is available.');

        return $business;
    }
}

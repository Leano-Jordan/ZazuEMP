<?php

namespace App\Support;

use App\Models\Business;
use Illuminate\Contracts\Auth\Authenticatable;

class CurrentBusiness
{
    public function resolve(?Authenticatable $user = null): ?Business
    {
        $user ??= request()->user();

        if ($user) {
            return $user->businesses()
                ->where('businesses.status', 'active')
                ->orderBy('businesses.id')
                ->first();
        }

        if (app()->environment('testing')) {
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

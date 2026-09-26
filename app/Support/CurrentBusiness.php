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

        if (app()->environment(['local', 'testing'])) {
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

<?php

namespace App\Support;

use App\Models\Business;
use Illuminate\Contracts\Auth\Authenticatable;

class PermissionService
{
    public function allows(string $permission, ?Authenticatable $user = null, ?Business $business = null): bool
    {
        $user ??= auth()->user();
        $business ??= app(CurrentBusiness::class)->resolve($user);

        if (!$user || !$business) {
            return false;
        }

        $membership = $user->businesses()
            ->whereKey($business->id)
            ->first();

        $role = $membership?->pivot?->role;

        if ($role === 'owner') {
            return true;
        }

        return in_array($permission, config('zazu.permissions.roles.'.$role, []), true);
    }
}
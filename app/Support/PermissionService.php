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

        $role = $user->businesses()
            ->whereKey($business->id)
            ->value('business_user.role');

        if ($role === 'owner') {
            return true;
        }

        return in_array($permission, config('zazu.permissions.roles.'.$role, []), true);
    }
}
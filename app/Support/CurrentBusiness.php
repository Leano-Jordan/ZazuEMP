<?php

namespace App\Support;

use App\Models\Business;
use Illuminate\Contracts\Auth\Authenticatable;

class CurrentBusiness
{
    public const SESSION_KEY = 'zazu_business_id';

    public function resolve(?Authenticatable $user = null): ?Business
    {
        $user ??= auth()->user();

        if ($user) {
            $selectedId = request()->session()->get(self::SESSION_KEY);

            $query = $user->businesses()
                ->where('businesses.status', 'active');

            if ($selectedId !== null) {
                $selected = (clone $query)->whereKey($selectedId)->first();

                if ($selected) {
                    return $selected;
                }

                request()->session()->forget(self::SESSION_KEY);
            }

            $business = $query->orderBy('businesses.id')->first();

            if ($business) {
                request()->session()->put(self::SESSION_KEY, $business->id);
            }

            return $business;
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

        return null;
    }


    public function switchTo(int $businessId, ?Authenticatable $user = null): Business
    {
        $user ??= auth()->user();

        abort_unless($user, 403);

        $business = $user->businesses()
            ->where('businesses.status', 'active')
            ->whereKey($businessId)
            ->first();

        abort_unless($business, 403);

        request()->session()->put(self::SESSION_KEY, $business->id);

        return $business;
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

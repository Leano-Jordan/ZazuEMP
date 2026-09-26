<?php

namespace App\Models\Concerns;

use App\Support\CurrentBusiness;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;

trait BelongsToBusiness
{
    protected static function bootBelongsToBusiness(): void
    {
        static::saving(function (Model $model): void {
            if (!auth()->check()) {
                return;
            }

            $business = app(CurrentBusiness::class)->resolve(auth()->user());

            if (!$business) {
                return;
            }

            $businessId = (int) $business->id;
            $modelBusinessId = $model->getAttribute('business_id');

            if ($modelBusinessId === null) {
                $model->setAttribute('business_id', $businessId);

                return;
            }

            if ((int) $modelBusinessId !== $businessId) {
                throw new AuthorizationException(
                    'The record does not belong to the active business workspace.'
                );
            }
        });
    }
}

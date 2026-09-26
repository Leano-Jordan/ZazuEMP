<?php

namespace App\Models\Concerns;

use App\Support\CurrentBusiness;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;

trait BelongsToBusiness
{
    protected static function bootBelongsToBusiness(): void
    {
        static::creating(function (Model $model): void {
            $context = app(CurrentBusiness::class)->resolve();

            if ($model->getAttribute('business_id') !== null) {
                if ($context && (int) $model->getAttribute('business_id') !== (int) $context->id) {
                    throw new AuthorizationException('The record does not belong to the active business.');
                }

                return;
            }

            if ($context) {
                $model->setAttribute('business_id', $context->id);
            }
        });
    }
}

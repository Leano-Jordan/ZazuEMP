<?php

namespace App\Models\Concerns;

use App\Support\CurrentBusiness;
use Illuminate\Database\Eloquent\Model;

trait BelongsToBusiness
{
    protected static function bootBelongsToBusiness(): void
    {
        static::creating(function (Model $model): void {
            if ($model->getAttribute('business_id') !== null) {
                return;
            }

            $model->setAttribute(
                'business_id',
                app(CurrentBusiness::class)->id()
            );
        });
    }
}

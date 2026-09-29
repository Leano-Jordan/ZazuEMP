<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformLandingSetting extends Model
{
    protected $fillable = [
        'hero_image_path',
        'operations_image_path',
        'resources_image_path',
        'control_image_path',
    ];

    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }
}

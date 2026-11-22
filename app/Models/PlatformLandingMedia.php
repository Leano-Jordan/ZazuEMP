<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformLandingMedia extends Model
{
    protected $fillable = [
        'label',
        'path',
        'mime_type',
        'file_size',
    ];
}

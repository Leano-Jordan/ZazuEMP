<?php

namespace App\Http\Controllers;

use App\Support\CurrentBusiness;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BusinessMediaController extends Controller
{
    public function show(Request $request, string $type): BinaryFileResponse
    {
        $business = app(CurrentBusiness::class)->model($request->user());

        $column = match ($type) {
            'logo' => 'logo_path',
            'dashboard' => 'dashboard_image_path',
            'wallpaper' => 'wallpaper_path',
            default => abort(404),
        };

        $path = $business->{$column};

        abort_unless(
            is_string($path)
            && Str::startsWith($path, 'business-branding/')
            && !Str::contains($path, ['..', '\\']),
            404
        );

        $disk = Storage::disk('public');
        abort_unless($disk->exists($path), 404);

        return response()->file($disk->path($path), [
            'Cache-Control' => 'private, max-age=300',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}

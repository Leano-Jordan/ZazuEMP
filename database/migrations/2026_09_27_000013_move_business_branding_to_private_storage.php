<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $public = Storage::disk('public');
        $private = Storage::disk('local');

        foreach (DB::table('businesses')
            ->select('id', 'logo_path', 'dashboard_image_path', 'wallpaper_path')
            ->orderBy('id')
            ->get() as $business) {
            foreach (['logo_path', 'dashboard_image_path', 'wallpaper_path'] as $column) {
                $path = $business->{$column};

                if (!is_string($path) || !Str::startsWith($path, 'business-branding/')) {
                    continue;
                }

                if (!$public->exists($path)) {
                    continue;
                }

                if (!$private->put($path, $public->get($path))) {
                    throw new RuntimeException('Unable to migrate business branding file: '.$path);
                }

                $public->delete($path);
            }
        }
    }

    public function down(): void
    {
        // Branding remains on private storage. Reversing this migration must
        // not silently make business files publicly accessible again.
    }
};

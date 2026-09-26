<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SecureProfileMedia extends Command
{
    protected $signature = 'zazu:secure-profile-media';
    protected $description = 'Move legacy profile photos from public storage to private storage';

    public function handle(): int
    {
        $public = Storage::disk('public');
        $private = Storage::disk('local');

        $moved = 0;

        foreach ([
            Customer::query()->whereNotNull('profile_photo_path')->cursor(),
            User::query()->whereNotNull('profile_photo_path')->cursor(),
        ] as $records) {
            foreach ($records as $record) {
                $path = $record->profile_photo_path;

                if (!is_string($path) || !Str::startsWith($path, [
                    'profile-photos/customers/',
                    'profile-photos/users/',
                ])) {
                    continue;
                }

                if (!$public->exists($path)) {
                    continue;
                }

                if (!$private->exists($path)) {
                    $private->put($path, $public->get($path));
                }

                $public->delete($path);
                $moved++;
            }
        }

        $this->info("Profile media secured. Moved: {$moved}.");

        return self::SUCCESS;
    }
}

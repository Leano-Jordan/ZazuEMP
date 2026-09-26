<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use App\Support\CurrentBusiness;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProfileMediaController extends Controller
{
    public function show(Request $request, string $type, int $id): BinaryFileResponse
    {
        $business = app(CurrentBusiness::class)->model($request->user());

        if ($type === 'customer') {
            $owner = Customer::query()
                ->whereKey($id)
                ->where('business_id', $business->id)
                ->firstOrFail();
            $path = $owner->profile_photo_path;
            $prefix = 'profile-photos/customers/';
        } elseif ($type === 'user') {
            $owner = User::query()->whereKey($id)->firstOrFail();
            abort_unless(
                $owner->businesses()->whereKey($business->id)->where('businesses.status', 'active')->exists(),
                404
            );
            $path = $owner->profile_photo_path;
            $prefix = 'profile-photos/users/';
        } else {
            abort(404);
        }

        abort_unless(
            is_string($path) &&
            Str::startsWith($path, $prefix) &&
            !Str::contains($path, ['..', '\\']),
            404
        );

        $private = Storage::disk('local');

        if (!$private->exists($path)) {
            $public = Storage::disk('public');

            if ($public->exists($path)) {
                $private->put($path, $public->get($path));
                $public->delete($path);
            }
        }

        abort_unless($private->exists($path), 404);

        return response()->file($private->path($path), [
            'Cache-Control' => 'private, max-age=300',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}

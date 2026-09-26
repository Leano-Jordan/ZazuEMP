<?php

namespace App\Services;

use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegisterBusiness
{
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => trim($data['name']),
                'email' => strtolower(trim($data['email'])),
                'password' => $data['password'],
            ]);

            $business = Business::create([
                'name' => trim($data['business_name']),
                'slug' => Str::slug($data['business_name']).'-'.Str::lower(Str::random(8)),
                'status' => 'active',
                'currency' => 'ZAR',
            ]);

            $business->users()->attach($user->id, [
                'role' => 'owner',
            ]);

            return $user;
        });
    }
}

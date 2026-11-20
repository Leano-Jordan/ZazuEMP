<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoBusinessSwitchSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $owner = User::query()
            ->where('email', (string) env('ZAZU_DEMO_EMAIL', 'demo@zazu.local'))
            ->firstOrFail();

        $business = Business::firstOrCreate(
            ['slug' => 'zazu-demo-secondary-workspace'],
            [
                'name' => 'Zazu Demo Secondary Workspace',
                'status' => 'active',
                'currency' => 'ZAR',
                'business_setup_completed_at' => now(),
                'catalogue_setup_completed_at' => now(),
            ]
        );

        $business->users()->syncWithoutDetaching([
            $owner->id => [
                'role' => 'owner',
                'experience_level' => 'intermediate',
            ],
        ]);
    }
}

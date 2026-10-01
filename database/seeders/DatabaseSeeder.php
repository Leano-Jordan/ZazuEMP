<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Keep the normal database seed safe and lightweight. Opt into the
        // full local demo scenario explicitly with ZAZU_SEED_DEMO=true.
        User::factory()->create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
        ]);

        if (app()->environment('local') && filter_var(env('ZAZU_SEED_DEMO', false), FILTER_VALIDATE_BOOL)) {
            $this->call(DemoScenarioSeeder::class);
        }
    }
}

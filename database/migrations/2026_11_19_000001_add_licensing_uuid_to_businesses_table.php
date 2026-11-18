<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table): void {
            $table->uuid('licensing_uuid')->nullable()->unique();
        });

        DB::table('businesses')
            ->whereNull('licensing_uuid')
            ->orderBy('id')
            ->chunkById(100, function ($businesses): void {
                foreach ($businesses as $business) {
                    DB::table('businesses')
                        ->where('id', $business->id)
                        ->whereNull('licensing_uuid')
                        ->update(['licensing_uuid' => (string) Str::uuid()]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table): void {
            $table->dropUnique('businesses_licensing_uuid_unique');
            $table->dropColumn('licensing_uuid');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_capabilities', function (Blueprint $table) {
            $table->char('currency', 3)->default('ZAR')->after('default_price');
        });

        DB::table('business_capabilities')
            ->orderBy('id')
            ->chunkById(100, function ($capabilities): void {
                foreach ($capabilities as $capability) {
                    $currency = DB::table('businesses')
                        ->where('id', $capability->business_id)
                        ->value('currency') ?? 'ZAR';

                    DB::table('business_capabilities')
                        ->where('id', $capability->id)
                        ->update(['currency' => strtoupper($currency)]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('business_capabilities', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
};

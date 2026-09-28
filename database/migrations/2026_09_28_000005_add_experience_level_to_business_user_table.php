<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_user', function (Blueprint $table) {
            $table->string('experience_level')->nullable()->after('role')->index();
        });

        DB::table('business_user')
            ->whereNull('experience_level')
            ->update(['experience_level' => 'intermediate']);
    }

    public function down(): void
    {
        Schema::table('business_user', function (Blueprint $table) {
            $table->dropIndex(['experience_level']);
            $table->dropColumn('experience_level');
        });
    }
};

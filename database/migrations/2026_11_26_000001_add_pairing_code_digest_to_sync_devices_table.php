<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sync_devices', function (Blueprint $table): void {
            $table->char('pairing_code_digest', 64)->nullable();
            $table->unique('pairing_code_digest');
        });
    }

    public function down(): void
    {
        Schema::table('sync_devices', function (Blueprint $table): void {
            $table->dropUnique(['pairing_code_digest']);
            $table->dropColumn('pairing_code_digest');
        });
    }
};

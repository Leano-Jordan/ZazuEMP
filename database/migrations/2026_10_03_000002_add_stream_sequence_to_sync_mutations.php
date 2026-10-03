<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sync_mutations', function (Blueprint $table): void {
            $table->string('stream', 64)->default('business')->after('sync_device_id');
            $table->unsignedBigInteger('sequence')->nullable()->after('mutation_id');

            $table->unique(['business_id', 'stream', 'sequence']);
            $table->index(['sync_device_id', 'stream', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::table('sync_mutations', function (Blueprint $table): void {
            $table->dropUnique(['business_id', 'stream', 'sequence']);
            $table->dropIndex(['sync_device_id', 'stream', 'sequence']);
            $table->dropColumn(['stream', 'sequence']);
        });
    }
};

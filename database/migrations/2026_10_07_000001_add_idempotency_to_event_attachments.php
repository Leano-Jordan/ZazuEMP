<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_attachments', function (Blueprint $table): void {
            $table->uuid('idempotency_key')->nullable()->after('id');
            $table->unique(['business_id', 'idempotency_key']);
        });
    }

    public function down(): void
    {
        Schema::table('event_attachments', function (Blueprint $table): void {
            $table->dropUnique(['business_id', 'idempotency_key']);
            $table->dropColumn('idempotency_key');
        });
    }
};

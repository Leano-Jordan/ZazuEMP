<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->foreignId('quote_version_id')
                ->nullable()
                ->after('quote_id')
                ->constrained('quote_versions')
                ->nullOnDelete();

            $table->unique('quote_version_id');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropUnique('invoices_quote_version_id_unique');
            $table->dropForeign(['quote_version_id']);
            $table->dropColumn('quote_version_id');
        });
    }
};

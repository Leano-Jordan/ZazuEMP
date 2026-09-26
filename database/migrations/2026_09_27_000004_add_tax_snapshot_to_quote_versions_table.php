<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quote_versions', function (Blueprint $table) {
            $table->foreignId('tax_rate_id')->nullable()->constrained('tax_rates')->nullOnDelete()->after('notes');
            $table->string('tax_code')->nullable()->after('tax_rate_id');
            $table->string('tax_label')->nullable()->after('tax_code');
            $table->string('tax_treatment')->nullable()->after('tax_label');
            $table->decimal('tax_rate', 5, 2)->default(0)->after('tax_treatment');
            $table->timestamp('tax_snapshot_at')->nullable()->after('tax_rate');
        });
    }

    public function down(): void
    {
        Schema::table('quote_versions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tax_rate_id');
            $table->dropColumn(['tax_code', 'tax_label', 'tax_treatment', 'tax_rate', 'tax_snapshot_at']);
        });
    }
};
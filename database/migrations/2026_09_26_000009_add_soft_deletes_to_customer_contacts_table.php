<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_contacts', function (Blueprint $table) {
            $table->softDeletes();
            $table->index(['customer_id', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::table('customer_contacts', function (Blueprint $table) {
            $table->dropIndex(['customer_id', 'deleted_at']);
            $table->dropSoftDeletes();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_version_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_requirement_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('capability_id')->nullable()->constrained('business_capabilities')->nullOnDelete();
            $table->string('description');
            $table->decimal('quantity', 12, 2)->default(1);
            $table->string('unit')->nullable();
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->string('pricing_basis')->nullable();
            $table->json('source_snapshot')->nullable();
            $table->timestamps();

            $table->index(['quote_version_id', 'event_requirement_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_items');
    }
};
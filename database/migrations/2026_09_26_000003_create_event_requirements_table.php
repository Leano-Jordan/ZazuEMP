<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('capability_id')->nullable()->constrained('business_capabilities')->nullOnDelete();
            $table->string('description');
            $table->string('category')->nullable();
            $table->decimal('quantity', 12, 2)->default(1);
            $table->string('unit')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('open');
            $table->timestamps();

            $table->index(['event_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_requirements');
    }
};

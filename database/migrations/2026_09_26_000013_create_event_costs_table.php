<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('category', 100);
            $table->string('description');
            $table->string('currency', 3)->default('ZAR');
            $table->decimal('projected_amount', 12, 2)->default(0);
            $table->decimal('actual_amount', 12, 2)->nullable();
            $table->string('status', 30)->default('planned');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'event_id']);
            $table->index(['event_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_costs');
    }
};
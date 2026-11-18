<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_number_sequences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->char('period', 6);
            $table->unsignedBigInteger('next_number')->default(1);
            $table->timestamps();
            $table->unique(['business_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_number_sequences');
    }
};

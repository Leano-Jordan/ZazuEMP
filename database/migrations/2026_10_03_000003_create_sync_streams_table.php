<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_streams', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('stream', 64);
            $table->unsignedBigInteger('next_sequence')->default(1);
            $table->timestamps();
            $table->unique(['business_id', 'stream']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_streams');
    }
};

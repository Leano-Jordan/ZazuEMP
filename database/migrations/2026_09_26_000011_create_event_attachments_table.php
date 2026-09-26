<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('original_name');
            $table->string('disk')->default('private');
            $table->string('path');
            $table->string('mime_type', 150)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('source', 30)->default('upload');
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_attachments');
    }
};
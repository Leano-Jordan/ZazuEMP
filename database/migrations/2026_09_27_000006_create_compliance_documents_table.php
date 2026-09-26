<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('document_type');
            $table->string('title');
            $table->string('reference_number')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('status')->default('current');
            $table->string('storage_path')->nullable();
            $table->text('source_reference')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'document_type', 'status']);
            $table->index(['business_id', 'expiry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_documents');
    }
};
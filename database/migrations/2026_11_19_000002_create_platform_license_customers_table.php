<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_license_customers', function (Blueprint $table): void {
            $table->id();
            $table->uuid('business_uuid')->unique();
            $table->string('business_name');
            $table->string('contact_email')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->uuid('last_license_id')->nullable()->unique();
            $table->string('last_plan', 40)->nullable();
            $table->string('last_status', 24)->nullable();
            $table->timestamp('last_starts_at')->nullable();
            $table->timestamp('last_expires_at')->nullable();
            $table->timestamp('last_issued_at')->nullable();
            $table->timestamps();

            $table->index(['last_status', 'last_expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_license_customers');
    }
};

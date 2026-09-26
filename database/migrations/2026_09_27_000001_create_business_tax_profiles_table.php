<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_tax_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('legal_name')->nullable();
            $table->string('trading_name')->nullable();
            $table->string('registration_type')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('tax_regime')->default('standard_income_tax');
            $table->string('income_tax_number')->nullable();
            $table->string('vat_status')->default('not_registered');
            $table->string('vat_number')->nullable();
            $table->string('paye_number')->nullable();
            $table->string('uif_number')->nullable();
            $table->string('sdl_number')->nullable();
            $table->date('financial_year_end')->nullable();
            $table->string('representative_name')->nullable();
            $table->string('representative_email')->nullable();
            $table->string('representative_phone')->nullable();
            $table->string('tcs_reference')->nullable();
            $table->text('tcs_pin')->nullable();
            $table->timestamp('tcs_pin_expires_at')->nullable();
            $table->timestamp('tcs_last_checked_at')->nullable();
            $table->json('activity_flags')->nullable();
            $table->text('compliance_notes')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'vat_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_tax_profiles');
    }
};
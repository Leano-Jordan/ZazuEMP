<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            $table->string('reference')->unique();

            $table->string('name');

            $table->string('event_type')->nullable();

            $table->string('customer_name');

            $table->string('customer_phone')->nullable();

            $table->string('customer_email')->nullable();

            $table->date('event_date')->nullable();

            $table->string('event_address')->nullable();

            $table->text('notes')->nullable();

            $table->string('status')->default('draft');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
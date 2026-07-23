<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receiving_numbers', function (Blueprint $table) {
            $table->id();
            $table->enum('network', ['safaricom', 'airtel']);
            $table->string('msisdn');
            $table->boolean('active')->default(true);
            $table->unsignedInteger('daily_limit')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receiving_numbers');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('c2b_validation_log', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->nullable();
            $table->string('msisdn')->nullable();
            $table->unsignedInteger('amount')->nullable();
            $table->enum('decision', ['accepted', 'rejected']);
            $table->text('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('c2b_validation_log');
    }
};

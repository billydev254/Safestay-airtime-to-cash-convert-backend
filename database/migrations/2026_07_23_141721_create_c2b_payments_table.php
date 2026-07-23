<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('c2b_payments', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->string('transaction_type')->nullable();
            $table->string('trans_time')->nullable();
            $table->unsignedInteger('amount')->nullable();
            $table->string('business_shortcode')->nullable();
            $table->string('bill_ref_number')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('msisdn')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->text('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('c2b_payments');
    }
};

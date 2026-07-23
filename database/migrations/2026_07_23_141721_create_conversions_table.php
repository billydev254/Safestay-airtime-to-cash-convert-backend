<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['airtime', 'bonga']);
            $table->enum('network', ['safaricom', 'airtel'])->nullable();
            $table->string('sender_number');
            $table->string('mpesa_number');
            $table->unsignedInteger('amount_in');
            $table->unsignedTinyInteger('cashback_pct');
            $table->unsignedInteger('amount_payout');
            $table->enum('status', [
                'pending',
                'awaiting_intake',
                'paying',
                'paid',
                'payout_failed',
                'rejected',
            ])->default('pending');
            $table->string('originator_conversation_id')->nullable()->unique();
            $table->string('mpesa_receipt')->nullable();
            $table->string('payout_result_desc')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversions');
    }
};

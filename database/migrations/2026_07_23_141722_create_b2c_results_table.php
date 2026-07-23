<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('b2c_results', function (Blueprint $table) {
            $table->id();
            $table->string('originator_conversation_id')->unique();
            $table->string('conversation_id')->nullable();
            $table->string('transaction_id')->nullable();
            $table->integer('result_code')->nullable();
            $table->string('result_desc')->nullable();
            $table->unsignedInteger('amount')->nullable();
            $table->string('receipt')->nullable();
            $table->string('receiver_name')->nullable();
            $table->string('utility_balance')->nullable();
            $table->string('completed_at')->nullable();
            $table->text('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('b2c_results');
    }
};

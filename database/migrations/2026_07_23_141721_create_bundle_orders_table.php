<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bundle_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bundle_id')->constrained()->cascadeOnDelete();
            $table->string('recipient_number');
            $table->string('mpesa_number');
            $table->unsignedInteger('amount');
            $table->string('checkout_request_id')->nullable()->unique();
            $table->string('mpesa_receipt')->nullable();
            $table->enum('status', ['pending_payment', 'paid', 'failed'])->default('pending_payment');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bundle_orders');
    }
};

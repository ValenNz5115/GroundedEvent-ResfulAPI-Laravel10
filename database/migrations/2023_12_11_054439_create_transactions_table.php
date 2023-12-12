<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id('transaction_id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('event_id');
            $table->date('transaction_date');
            $table->date('payment_date')->nullable();
            $table->enum('status_ordered', ['process','order', 'finished', 'cancelled'])->default('process');
            $table->enum('status_payment', ['waiting', 'paid'])->default('waiting');

            $table->timestamps();


            $table->foreign('customer_id')->references('customer_id')->on('customers');
            $table->foreign('event_id')->references('event_id')->on('events');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

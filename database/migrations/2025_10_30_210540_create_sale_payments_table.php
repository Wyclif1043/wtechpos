<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_sale_payments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->string('payment_method'); // cash, card, mobile_money, credit, check, gift_card
            $table->decimal('amount', 10, 2);
            $table->string('reference')->nullable(); // transaction ID, check number, etc.
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Processed by
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sale_payments');
    }
};
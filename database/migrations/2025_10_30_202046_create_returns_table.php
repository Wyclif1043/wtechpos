<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_returns_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Processed by
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('refund_amount', 10, 2)->default(0);
            $table->string('reason'); // defective, wrong_item, changed_mind, duplicate, other
            $table->text('notes')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected, completed
            $table->string('refund_method')->nullable(); // original, cash, card, credit_note
            $table->string('refund_reference')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('returns');
    }
};
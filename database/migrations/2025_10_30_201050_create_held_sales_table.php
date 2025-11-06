<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_held_sales_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('held_sales', function (Blueprint $table) {
            $table->id();
            $table->string('hold_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->json('cart_data'); // Store entire cart data
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('held_at')->useCurrent();
            $table->timestamp('expires_at')->useCurrent()->useCurrentOnUpdate();
            $table->string('status')->default('held');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('held_sales');
    }
};
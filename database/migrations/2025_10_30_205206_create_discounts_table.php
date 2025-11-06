<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_discounts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // percentage, fixed_amount, buy_x_get_y
            $table->decimal('value', 10, 2)->default(0); // Percentage or fixed amount
            $table->string('scope'); // sale, product, category, customer
            $table->json('scope_ids')->nullable(); // Specific products/categories/customers
            $table->decimal('min_purchase_amount', 10, 2)->nullable();
            $table->integer('min_quantity')->nullable();
            $table->integer('max_uses')->nullable();
            $table->integer('used_count')->default(0);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true);
            $table->boolean('apply_automatically')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('discounts');
    }
};
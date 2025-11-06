<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_daily_sales_summaries_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('daily_sales_summaries', function (Blueprint $table) {
            $table->id();
            $table->date('sale_date');
            $table->integer('total_sales')->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('total_tax', 15, 2)->default(0);
            $table->decimal('total_discount', 15, 2)->default(0);
            $table->integer('total_items_sold')->default(0);
            $table->decimal('cash_sales', 15, 2)->default(0);
            $table->decimal('card_sales', 15, 2)->default(0);
            $table->decimal('mobile_money_sales', 15, 2)->default(0);
            $table->decimal('credit_sales', 15, 2)->default(0);
            $table->timestamps();

            $table->unique('sale_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('daily_sales_summaries');
    }
};
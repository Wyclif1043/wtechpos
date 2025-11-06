<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_product_performance_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_performance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->date('sale_date');
            $table->integer('quantity_sold')->default(0);
            $table->decimal('revenue', 15, 2)->default(0);
            $table->decimal('profit', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['sale_date', 'product_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_performance');
    }
};
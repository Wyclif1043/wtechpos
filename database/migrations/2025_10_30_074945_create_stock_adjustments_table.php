<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_stock_adjustments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_number')->unique();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->string('type'); // add, remove
            $table->string('reason'); // damaged, expired, found, etc.
            $table->text('description')->nullable();
            $table->integer('previous_stock');
            $table->integer('new_stock');
            $table->decimal('cost_value', 10, 2)->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_sale_discounts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sale_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('discount_id')->nullable()->constrained()->onDelete('set null');
            $table->string('discount_name');
            $table->string('discount_type'); // percentage, fixed_amount, manual
            $table->decimal('discount_value', 10, 2);
            $table->decimal('discount_amount', 10, 2);
            $table->string('applied_to'); // sale, item, shipping
            $table->unsignedBigInteger('applied_to_id')->nullable(); // sale_item_id or null for entire sale
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sale_discounts');
    }
};
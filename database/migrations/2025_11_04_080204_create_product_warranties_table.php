<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_warranties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('warranty_name');
            $table->enum('type', ['manufacturer', 'store', 'extended'])->default('store');
            $table->integer('duration_months');
            $table->text('terms')->nullable();
            $table->text('coverage_details')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['product_id', 'warranty_name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_warranties');
    }
};
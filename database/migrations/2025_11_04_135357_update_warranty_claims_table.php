<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('warranty_claims', function (Blueprint $table) {
            // Add missing columns
            $table->foreignId('sale_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('sale_item_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('product_warranty_id')->nullable()->constrained()->onDelete('cascade');
            
            // Remove the old warranty_id if it exists
            if (Schema::hasColumn('warranty_claims', 'warranty_id')) {
                $table->dropForeign(['warranty_id']);
                $table->dropColumn('warranty_id');
            }
        });
    }

    public function down()
    {
        Schema::table('warranty_claims', function (Blueprint $table) {
            $table->dropForeign(['sale_id']);
            $table->dropForeign(['sale_item_id']);
            $table->dropForeign(['product_warranty_id']);
            
            $table->dropColumn(['sale_id', 'sale_item_id', 'product_warranty_id']);
        });
    }
};
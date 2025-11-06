<?php
// database/migrations/xxxx_xx_xx_xxxxxx_update_sales_table_for_split_payments.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            // Remove old payment columns
            $table->dropColumn(['payment_method', 'amount_paid', 'change_amount']);
            
            // Add new payment tracking columns
            $table->decimal('total_paid', 10, 2)->default(0)->after('total_amount');
            $table->decimal('balance_due', 10, 2)->default(0)->after('total_paid');
            $table->string('payment_status')->default('pending')->after('balance_due'); // pending, partial, paid, refunded
        });
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['total_paid', 'balance_due', 'payment_status']);
            
            // Restore old columns
            $table->string('payment_method')->default('cash')->after('notes');
            $table->decimal('amount_paid', 10, 2)->default(0)->after('payment_method');
            $table->decimal('change_amount', 10, 2)->default(0)->after('amount_paid');
        });
    }
};
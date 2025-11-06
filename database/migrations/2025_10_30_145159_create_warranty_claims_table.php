<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_warranty_claims_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('warranty_claims', function (Blueprint $table) {
            $table->id();
            $table->string('claim_number')->unique();
            $table->foreignId('warranty_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('sale_item_id')->constrained()->onDelete('cascade');
            $table->date('claim_date');
            $table->string('issue_type');
            $table->text('problem_description');
            $table->text('resolution_notes')->nullable();
            $table->decimal('repair_cost', 10, 2)->nullable()->default(0);
            $table->string('status')->default('submitted');
            $table->date('resolution_date')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('customer_feedback')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('warranty_claims');
    }
};
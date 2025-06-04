<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            $table->string('g_number');
            $table->date('date');
            $table->date('last_change_date');

            $table->string('supplier_article');
            $table->string('tech_size');

            $table->bigInteger('barcode');
            $table->string('total_price');
            $table->string('discount_percent');

            $table->boolean('is_supply');
            $table->boolean('is_realization');

            $table->string('promo_code_discount')->nullable();
            $table->string('warehouse_name');
            $table->string('country_name');
            $table->string('oblast_okrug_name')->nullable();
            $table->string('region_name');

            $table->unsignedBigInteger('income_id');
            $table->string('sale_id');
            $table->unsignedBigInteger('odid')->nullable();

            $table->string('spp');
            $table->string('for_pay');
            $table->string('finished_price');
            $table->string('price_with_disc');

            $table->bigInteger('nm_id');
            $table->string('subject');
            $table->string('category');
            $table->string('brand');

            $table->boolean('is_storno')->nullable();

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};

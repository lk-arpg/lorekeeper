<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrencyConversionTables extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('currency_conversions', function (Blueprint $table) {
            $table->integer('currency_id')->unsigned();
            $table->integer('conversion_id')->unsigned();
            $table->decimal('rate', 10, 2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('currency_conversions');
    }
}

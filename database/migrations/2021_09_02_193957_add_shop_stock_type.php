<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShopStockType extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        //
        Schema::table('shop_stock', function (Blueprint $table) {
            $table->string('stock_type')->default('Item');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        //
        Schema::table('shop_stock', function (Blueprint $table) {
            $table->dropColumn('stock_type');
        });
    }
}

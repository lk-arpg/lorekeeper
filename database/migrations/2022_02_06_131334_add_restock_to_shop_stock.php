<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRestockToShopStock extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('shop_stock', function (Blueprint $table) {
            //
            $table->boolean('restock')->default(false);
            $table->unsignedInteger('restock_quantity')->default(1);
            $table->unsignedInteger('restock_interval')->default(2);
            $table->boolean('range')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('shop_stock', function (Blueprint $table) {
            //
            $table->dropColumn('restock');
            $table->dropColumn('restock_quantity');
            $table->dropColumn('restock_interval');
            $table->dropColumn('range');
        });
    }
}

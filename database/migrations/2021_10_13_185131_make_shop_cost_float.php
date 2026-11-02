<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeShopCostFloat extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        //
        Schema::table('shop_stock', function (Blueprint $table) {
            $table->float('cost')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        //
        Schema::table('shop_stock', function (Blueprint $table) {
            $table->integer('cost')->change();
        });
    }
}

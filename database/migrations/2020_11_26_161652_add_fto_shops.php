<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFtoShops extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        //
        Schema::table('shops', function (Blueprint $table) {
            $table->boolean('is_fto')->default(0);
        });

        Schema::table('shop_stock', function (Blueprint $table) {
            $table->boolean('is_fto')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        //
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('is_fto');
        });

        Schema::table('shop_stock', function (Blueprint $table) {
            $table->dropColumn('is_fto');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVisbleToStock extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('shop_stock', function (Blueprint $table) {
            //
            $table->boolean('is_visible')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('shop_stock', function (Blueprint $table) {
            //
            $table->dropColumn('is_visible');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAllowedCouponsToShops extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('shops', function (Blueprint $table) {
            //
            $table->string('allowed_coupons')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('shops', function (Blueprint $table) {
            //
            $table->dropColumn('allowed_coupons');
        });
    }
}

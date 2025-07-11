<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdminShops extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        //
        Schema::table('shops', function (Blueprint $table) {
            $table->boolean('is_staff')->default(0);
            $table->boolean('use_coupons')->default(0);
            $table->boolean('is_restricted')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        //
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('is_staff');
            $table->dropColumn('use_coupons');
            $table->dropColumn('is_restricted');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimedShop extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('shops', function (Blueprint $table) {
            $table->boolean('is_timed_shop')->default(false);
            $table->timestamps();
            $table->timestamp('start_at')->nullable()->default(null);
            $table->timestamp('end_at')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('is_timed_shop');
            $table->dropColumn('start_at');
            $table->dropColumn('start_at');
        });
    }
}

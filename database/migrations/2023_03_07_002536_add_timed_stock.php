<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimedStock extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('shop_stock', function (Blueprint $table) {
            $table->boolean('is_timed_stock')->default(false);
            $table->timestamps();
            $table->timestamp('start_at')->nullable()->default(null);
            $table->timestamp('end_at')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('shop_stock', function (Blueprint $table) {
            $table->dropColumn('is_timed_stock');
            $table->dropColumn('start_at');
            $table->dropColumn('start_at');
        });
    }
}

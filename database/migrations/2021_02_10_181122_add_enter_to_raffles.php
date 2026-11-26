<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEnterToRaffles extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('raffles', function (Blueprint $table) {
            //
            $table->boolean('allow_entry')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('raffles', function (Blueprint $table) {
            $table->dropColumn('allow_entry');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddResetDayToTables extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        // Add reset_day to prompts table
        Schema::table('prompts', function (Blueprint $table) {
            $table->tinyInteger('reset_day')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        // Remove reset_day from prompts table
        Schema::table('prompts', function (Blueprint $table) {
            $table->dropColumn('reset_day');
        });
    }
}

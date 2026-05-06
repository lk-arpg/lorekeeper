<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeBirthdayColumnType extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        //
        Schema::table('users', function (Blueprint $table) {
            $table->datetime('birthday')->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        // #
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('birthday')->default(null)->change();
        });
    }
}

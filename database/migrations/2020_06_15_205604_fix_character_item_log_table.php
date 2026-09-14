<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixCharacterItemLogTable extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        DB::statement('ALTER TABLE character_items_log CHANGE `count` `quantity` INT(10) unsigned;');

        Schema::table('character_items_log', function (Blueprint $table) {
            $table->integer('stack_id')->unsigned()->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        DB::statement('ALTER TABLE character_items_log CHANGE `quantity` `count` INT(10) unsigned;');

        Schema::table('character_items_log', function (Blueprint $table) {
            $table->integer('stack_id')->unsigned()->change();
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AdjustItemLogsForCharacterItems extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        // Drop character item logs table in favor of adjusting existing logs table to suit
        Schema::dropIfExists('character_items_log');

        Schema::rename('user_items_log', 'items_log');

        Schema::table('items_log', function (Blueprint $table) {
            // Add sender and recipient type. Set default user to account for preexisting rows
            $table->enum('sender_type', ['User', 'Character'])->nullable()->default('User');
            $table->enum('recipient_type', ['User', 'Character'])->nullable()->default('User');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        //
        Schema::create('character_items_log', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('item_id')->unsigned();
            $table->integer('quantity')->unsigned()->default(1);
            $table->integer('stack_id')->unsigned()->nullable;

            $table->integer('sender_id')->unsigned()->nullable();
            $table->integer('recipient_id')->unsigned()->nullable();
            $table->string('log'); // Actual log text
            $table->string('log_type'); // Indicates what type of transaction the item was used in
            $table->string('data', 1024)->nullable(); // Includes information like staff notes, etc.

            $table->timestamps();

            $table->enum('sender_type', ['User', 'Character'])->nullable()->default(null);
            $table->enum('recipient_type', ['User', 'Character'])->nullable()->default(null);
        });

        Schema::rename('items_log', 'user_items_log');

        Schema::table('user_items_log', function (Blueprint $table) {
            // There isn't actually undoing the renaming of the keys but we live with that
            $table->dropColumn('sender_type');
            $table->dropColumn('recipient_type');
        });
    }
}

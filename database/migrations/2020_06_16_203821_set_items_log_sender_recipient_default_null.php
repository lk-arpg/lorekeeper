<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SetItemsLogSenderRecipientDefaultNull extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        // Change default to null going forward
        DB::statement("ALTER TABLE items_log CHANGE COLUMN sender_type sender_type ENUM('User', 'Character') DEFAULT NULL");
        DB::statement("ALTER TABLE items_log CHANGE COLUMN recipient_type recipient_type ENUM('User', 'Character') DEFAULT NULL");

        Schema::table('items_log', function (Blueprint $table) {
            // Actually drop them this time, please. Also drop the item_id column
            $table->dropForeign('inventory_log_sender_id_foreign');
            $table->dropForeign('inventory_log_recipient_id_foreign');
            $table->dropForeign('user_items_log_stack_id_foreign');
            $table->dropForeign('inventory_log_item_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        //
        DB::statement("ALTER TABLE items_log CHANGE COLUMN sender_type sender_type ENUM('User', 'Character') DEFAULT 'User'");
        DB::statement("ALTER TABLE items_log CHANGE COLUMN recipient_type recipient_type ENUM('User', 'Character') DEFAULT 'User'");

        Schema::table('items_log', function (Blueprint $table) {
            $table->foreign('sender_id')->references('id')->on('users');
            $table->foreign('recipient_id')->references('id')->on('users');
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('stack_id')->references('id')->on('user_items');
        });
    }
}

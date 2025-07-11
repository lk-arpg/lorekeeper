<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSenderRecipientTypeToCharacterItemsLog extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('character_items_log', function (Blueprint $table) {
            // Add sender and recipient type
            $table->enum('sender_type', ['User', 'Character'])->nullable()->default(null);
            $table->enum('recipient_type', ['User', 'Character'])->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('character_items_log', function (Blueprint $table) {
            //
            $table->dropColumn('sender_type');
            $table->dropColumn('recipient_type');
        });
    }
}

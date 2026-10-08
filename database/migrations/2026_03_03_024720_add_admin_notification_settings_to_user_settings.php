<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdminNotificationSettingsToUserSettings extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->tinyInteger('admin_notifs')->default(1);
            $table->tinyInteger('admin_notifs_nr_size')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->dropColumn('admin_notifs');
            $table->dropColumn('admin_notifs_nr_size');
        });
    }
}

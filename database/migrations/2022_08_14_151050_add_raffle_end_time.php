<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRaffleEndTime extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        //
        Schema::table('raffles', function (Blueprint $table) {
            $table->timestamp('end_at')->nullable()->default(null);
            $table->boolean('roll_on_end')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        //
        Schema::table('raffles', function (Blueprint $table) {
            $table->dropColumn('end_at');
            $table->dropColumn('roll_on_end');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRerollToRaffles extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('raffles', function (Blueprint $table) {
            $table->boolean('unordered')->default(false);
        });

        Schema::table('raffle_tickets', function (Blueprint $table) {
            //
            $table->boolean('reroll')->default(false);
        });

        Schema::create('raffle_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('raffle_id');
            $table->integer('user_id');
            $table->integer('ticket_id');
            $table->string('reason');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('raffles', function (Blueprint $table) {
            $table->dropColumn('unordered');
        });

        Schema::table('raffle_tickets', function (Blueprint $table) {
            //
            $table->dropColumn('reroll');
        });

        Schema::dropIfExists('raffle_logs');
    }
}

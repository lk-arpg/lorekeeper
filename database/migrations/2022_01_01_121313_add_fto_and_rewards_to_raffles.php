<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFtoAndRewardsToRaffles extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('raffles', function (Blueprint $table) {
            //
            $table->boolean('is_fto')->default(false);
        });

        Schema::create('raffle_entry_rewards', function (Blueprint $table) {
            $table->integer('raffle_id')->unsigned();
            $table->string('rewardable_type');
            $table->integer('rewardable_id')->unsigned();
            $table->integer('quantity')->unsigned();
        });

        Schema::table('raffle_logs', function (Blueprint $table) {
            $table->integer('ticket_id')->nullable()->default(null)->change();
            $table->string('type')->default('Reroll');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('raffles', function (Blueprint $table) {
            //
            $table->dropColumn('is_fto');
        });

        Schema::dropIfExists('raffle_entry_rewards');

        Schema::table('raffle_logs', function (Blueprint $table) {
            $table->integer('ticket_id')->unsigned()->change();
            $table->dropColumn('type');
        });
    }
}

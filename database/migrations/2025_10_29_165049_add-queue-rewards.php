<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQueueRewards extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('queues', function (Blueprint $table) {
            $table->longtext('output')->nullable()->default(null);
            $table->integer('limit_concurrent')->nullable()->default(null);
        });

        Schema::table('queue_categories', function (Blueprint $table) {
            $table->integer('limit')->nullable()->default(null);
            $table->enum('limit_period', ['Hour', 'Day', 'Week', 'Month', 'Year'])->nullable()->default(null);
            $table->integer('limit_concurrent')->nullable()->default(null);
            $table->boolean('display')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->dropColumn('output');
            $table->dropColumn('limit_concurrent');
        });

        Schema::table('queue_categories', function (Blueprint $table) {
            $table->dropColumn('limit');
            $table->dropColumn('limit_period');
            $table->dropColumn('limit_concurrent');
            $table->dropColumn('display');
        });
    }
}

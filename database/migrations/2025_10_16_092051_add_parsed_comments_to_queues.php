<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParsedCommentsToQueues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->longText('checklist')->nullable()->default(null);
             $table->integer('limit')->nullable()->default(null);
            $table->enum('limit_period', ['Hour', 'Day', 'Week', 'Month', 'Year'])->nullable()->default(null);
        });
        Schema::table('queue_submissions', function (Blueprint $table) {
            $table->text('parsed_comments')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('queue_submissions', function (Blueprint $table) {
             $table->dropColumn('parsed_comments');
        });
    }
}

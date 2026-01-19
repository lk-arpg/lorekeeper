<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQueueMaker extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        // we're gonna be duplicating a few prompt tables,
        // which is probably suboptimal, but i'm unsure how much fuckery this ext will result in,
        // so it's better to separate the madness and make prompts not involved in that

        Schema::create('queue_categories', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('key', 30)->unique()->nullable()->default(null);

            $table->string('name');
            $table->text('description')->nullable()->default(null);
            $table->text('parsed_description')->nullable()->default(null);
            $table->integer('sort')->unsigned()->default(0);

            $table->boolean('has_image')->default(0);
            $table->string('hash', 10)->nullable()->default(null);

            $table->integer('limit')->nullable()->default(null);
            $table->enum('limit_period', ['Hour', 'Day', 'Week', 'Month', 'Year'])->nullable()->default(null);
            $table->integer('limit_concurrent')->nullable()->default(null);
            $table->boolean('display')->default(1);
        });

        Schema::create('queues', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name', 64);

            // The summary will be displayed on the world page,
            // with a link to a page that contains the full text of the queue.
            $table->string('summary', 256)->nullable()->default(null);
            $table->text('description')->nullable()->default(null);
            $table->text('parsed_description')->nullable()->default(null);

            // The active flag is overridden by the start_at and end_at timestamps,
            // i.e. if either or both of those timestamps are set,
            // it will have no effect.
            $table->boolean('is_active')->default(1);
            $table->timestamp('start_at')->nullable()->default(null);
            $table->timestamp('end_at')->nullable()->default(null);
            // When submitting a queue, the selectable list will only contain queues between
            // the start/end times and active queues.

            // This hides the queue from the world queue list before
            // the queue start_at time has been reached.
            $table->boolean('hide_before_start')->default(0);

            // This hides the queue from the world queue list after
            // the queue end_at time has been reached.
            $table->boolean('hide_after_end')->default(0);

            $table->text('form')->nullable()->default(null);
            $table->text('parsed_form')->nullable()->default(null);

            $table->string('queue_type')->nullable()->default(null);
            $table->json('data')->nullable()->default(null);

            $table->string('hash', 10)->nullable()->default(null);

            $table->integer('queue_category_id')->unsigned()->nullable();
            $table->string('prefix', 10)->nullable();
            $table->integer('hide_submissions')->unsigned()->default(0);
            $table->boolean('staff_only')->default(0);
            $table->boolean('has_image')->default(0);

            // Staff rank restrictions
            $table->json('staff_rank_ids')->nullable()->default(null);

            $table->json('output')->nullable()->default(null);
            $table->json('checklist')->nullable()->default(null);

            $table->integer('limit')->nullable()->default(null);
            $table->text('limit_period')->nullable()->default(null);
            $table->boolean('limit_character')->nullable()->default(null);
            $table->integer('limit_concurrent')->nullable()->default(null);
        });

        Schema::create('queue_submissions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('queue_id')->unsigned()->nullable()->index();

            $table->integer('user_id')->unsigned()->index();
            $table->integer('staff_id')->unsigned()->nullable()->default(null);

            $table->text('comments')->nullable()->default(null);
            $table->text('parsed_comments')->nullable()->default(null);

            $table->text('staff_comments')->nullable()->default(null);
            $table->text('parsed_staff_comments')->nullable()->default(null);

            $table->enum('status', ['Draft', 'Pending', 'Approved', 'Rejected'])->default('Draft');

            $table->json('data')->nullable()->default(null);

            $table->timestamps();
        });

        Schema::create('queue_submission_characters', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('queue_submission_id')->unsigned()->index();
            $table->integer('character_id')->unsigned()->index();

            $table->json('data')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::dropIfExists('queue_categories');
        Schema::dropIfExists('queue_submissions');
        Schema::dropIfExists('queue_submission_characters');
        Schema::dropIfExists('queues');
    }
}

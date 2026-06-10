<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCriteriaAdditions extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('prompt_criteria', function (Blueprint $table) {
            // gonna set this to nullable to not break anything
            // will default to the criteria's original currency if not set
            $table->integer('criterion_currency_id')->unsigned()->nullable()->default(null);
        });

        Schema::table('gallery_criteria', function (Blueprint $table) {
            // gonna set this to nullable to not break anything
            // will default to the criteria's original currency if not set
            $table->integer('criterion_currency_id')->unsigned()->nullable()->default(null);
        });

        // i had a better idea before going to set this up-- make criteria groups for admins to click to toggle different criteria groups
        // this isn't because i want something more flexible like this-- nope, definitely not
        Schema::create('criteria_defaults', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->string('summary', 256)->nullable()->default(null);
            $table->text('criteria_ids')->nullable()->default(null);
        });

        Schema::create('default_criteria', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('criterion_id')->unsigned();
            $table->integer('criteriondefault_id')->unsigned();
            $table->text('min_requirements')->nullable()->default(null);
            // gonna set this to nullable to not break anything
            // will default to the criteria's original currency if not set
            $table->integer('criterion_currency_id')->unsigned()->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::dropIfExists('criteria_defaults');
        Schema::dropIfExists('default_criteria');
        Schema::table('prompt_criteria', function (Blueprint $table) {
            $table->dropcolumn('criterion_currency_id');
        });
        Schema::table('prompt_criteria', function (Blueprint $table) {
            $table->dropcolumn('criterion_currency_id');
        });
    }
}

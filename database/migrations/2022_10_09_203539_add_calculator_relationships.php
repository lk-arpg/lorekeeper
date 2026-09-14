<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCalculatorRelationships extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::create('prompt_criteria', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('criterion_id')->unsigned();
            $table->integer('prompt_id')->unsigned();
            $table->text('min_requirements')->nullable()->default(null);
        });

        Schema::create('gallery_criteria', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('criterion_id')->unsigned();
            $table->integer('gallery_id')->unsigned();
            $table->text('min_requirements')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::dropIfExists('prompt_criteria');
        Schema::dropIfExists('gallery_criteria');
    }
}

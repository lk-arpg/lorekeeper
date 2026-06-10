<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCalculators extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::create('criteria', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('name');
            $table->boolean('is_active')->default(0);
            $table->integer('currency_id')->unsigned();
            $table->string('summary', 256)->nullable()->default(null);
            $table->boolean('is_guide_active')->default(0);
            $table->integer('base_value')->nullable()->default(null);

            $table->foreign('currency_id')->references('id')->on('currencies');
        });

        Schema::create('criterion_steps', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('name');
            $table->boolean('is_active')->default(0);
            $table->integer('criterion_id')->unsigned();
            // Inline help text when using the criterion
            $table->string('summary', 256)->nullable()->default(null);
            // Full description in the guide
            $table->text('description')->nullable()->default(null);
            $table->text('parsed_description')->nullable()->default(null);
            $table->enum('type', ['input', 'options', 'boolean'])->default('boolean');
            $table->enum('calc_type', ['additive', 'multiplicative'])->default('additive');
            $table->enum('input_calc_type', ['additive', 'multiplicative'])->nullable()->default(null);
            $table->integer('order')->unsigned()->default(1);
            // For a default example image for the guide
            $table->boolean('has_image')->default(0);

            $table->foreign('criterion_id')->references('id')->on('criteria');
        });

        Schema::create('criterion_step_options', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('name');
            $table->boolean('is_active')->default(0);
            $table->integer('criterion_step_id')->unsigned();
            // Inline help text when using the criterion
            $table->string('summary', 256)->nullable()->default(null);
            // Full description in the guide
            $table->text('description')->nullable()->default(null);
            $table->text('parsed_description')->nullable()->default(null);
            $table->float('amount')->nullable()->default(null);
            $table->integer('order')->unsigned()->default(1);

            $table->foreign('criterion_step_id')->references('id')->on('criterion_steps');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::dropIfExists('criterion_step_options');
        Schema::dropIfExists('criterion_steps');
        Schema::dropIfExists('criteria');
    }
}

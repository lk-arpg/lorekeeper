<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('currency_categories', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('name');
            $table->integer('sort')->default(0);
            $table->text('description')->nullable()->default(null);
            $table->text('parsed_description')->nullable()->default(null);

            $table->boolean('has_image')->default(0);
            $table->boolean('is_visible')->default(0);

            $table->string('hash', 10)->nullable()->default(null);
        });

        Schema::table('currencies', function (Blueprint $table) {
            $table->integer('currency_category_id')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('currency_categories');

        Schema::table('currencies', function (Blueprint $table) {
            $table->dropColumn('currency_category_id');
        });
    }
};

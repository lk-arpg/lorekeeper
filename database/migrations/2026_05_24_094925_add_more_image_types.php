<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('character_categories', function (Blueprint $table) {
            $table->string('image_extension')->nullable()->after('has_image')->default('png');
        });
        //
        Schema::table('items', function (Blueprint $table) {
            $table->string('image_extension')->nullable()->after('has_image')->default('png');
        });
        Schema::table('item_categories', function (Blueprint $table) {
            $table->string('image_extension')->nullable()->after('has_image')->default('png');
        });
        //
        Schema::table('features', function (Blueprint $table) {
            $table->string('image_extension')->nullable()->after('has_image')->default('png');
        });
        Schema::table('feature_categories', function (Blueprint $table) {
            $table->string('image_extension')->nullable()->after('has_image')->default('png');
        });
        //
        Schema::table('prompts', function (Blueprint $table) {
            $table->string('image_extension')->nullable()->after('has_image')->default('png');
        });
        Schema::table('prompt_categories', function (Blueprint $table) {
            $table->string('image_extension')->nullable()->after('has_image')->default('png');
        });
        //
        Schema::table('rarities', function (Blueprint $table) {
            $table->string('image_extension')->nullable()->after('has_image')->default('png');
        });
        //
        Schema::table('specieses', function (Blueprint $table) {
            $table->string('image_extension')->nullable()->after('has_image')->default('png');
        });
        Schema::table('subtypes', function (Blueprint $table) {
            $table->string('image_extension')->nullable()->after('has_image')->default('png');
        });
        //
        Schema::table('currencies', function (Blueprint $table) {
            $table->string('image_extension')->nullable()->after('has_image')->default('png');
            $table->string('icon_extension')->nullable()->after('has_icon')->default('png');
        });
        Schema::table('currency_categories', function (Blueprint $table) {
            $table->string('image_extension')->nullable()->after('has_image')->default('png');
        });
        //
        Schema::table('shops', function (Blueprint $table) {
            $table->string('image_extension')->nullable()->after('has_image')->default('png');
        });
        //
        Schema::table('news', function (Blueprint $table) {
            $table->string('image_extension')->nullable()->after('has_image')->default('png');
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->string('image_extension')->nullable()->after('has_image')->default('png');
        });
        Schema::table('site_pages', function (Blueprint $table) {
            $table->string('image_extension')->nullable()->after('has_image')->default('png');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('character_categories', function (Blueprint $table) {
            $table->dropIfExists('image_extension');
        });
        Schema::table('items', function (Blueprint $table) {
            $table->dropIfExists('image_extension');
        });
        Schema::table('item_categories', function (Blueprint $table) {
            $table->dropIfExists('image_extension');
        });
        //
        Schema::table('features', function (Blueprint $table) {
            $table->dropIfExists('image_extension');
        });
        Schema::table('feature_categories', function (Blueprint $table) {
            $table->dropIfExists('image_extension');
        });
        //
        Schema::table('prompts', function (Blueprint $table) {
            $table->dropIfExists('image_extension');
        });
        Schema::table('prompt_categories', function (Blueprint $table) {
            $table->dropIfExists('image_extension');
        });
        //
        Schema::table('rarities', function (Blueprint $table) {
            $table->dropIfExists('image_extension');
        });
        //
        Schema::table('specieses', function (Blueprint $table) {
            $table->dropIfExists('image_extension');
        });
        Schema::table('subtypes', function (Blueprint $table) {
            $table->dropIfExists('image_extension');
        });
        //
        Schema::table('currencies', function (Blueprint $table) {
            $table->dropIfExists(['image_extension', 'icon_extension']);
        });
        Schema::table('currency_categories', function (Blueprint $table) {
            $table->dropIfExists('image_extension');
        });
        //
        Schema::table('shops', function (Blueprint $table) {
            $table->dropIfExists('image_extension');
        });
        //
        Schema::table('news', function (Blueprint $table) {
            $table->dropIfExists('image_extension');
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIfExists('image_extension');
        });
        Schema::table('site_pages', function (Blueprint $table) {
            $table->dropIfExists('image_extension');
        });
    }
};

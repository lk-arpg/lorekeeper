<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('image_alt_text', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('object_id');
            $table->string('object_model');
            $table->string('alt_text', 500);
            $table->string('text_key')->default('Main'); // key to discriminate between images on models that would have 2+. it's niche but i like flexibility. plus, anything that makes this easier and more extensible...
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('image_alt_text');
    }
};

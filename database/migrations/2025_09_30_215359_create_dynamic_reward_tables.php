<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('rewards', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('object_id');
            $table->string('object_model');

            $table->string('rewardable_recipient')->default('User');
            $table->integer('rewardable_id');
            $table->string('rewardable_type');
            $table->integer('quantity');

            $table->json('data')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('rewards');
    }
};

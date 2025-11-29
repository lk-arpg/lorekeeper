<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('rewards', function (Blueprint $table) {
            $table->integer('rewardable_id')->nullable(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        // i don't recommend you reverse this, but...
        Schema::table('rewards', function (Blueprint $table) {
            $table->integer('rewardable_id')->nullable(false)->change();
        });
    }
};

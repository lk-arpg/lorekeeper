<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        //
        if (!Schema::hasTable('feature_subtypes')) {
            Schema::create('feature_subtypes', function (Blueprint $table) {
                $table->integer('feature_id')->unsigned();
                $table->integer('subtype_id')->unsigned();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        //
        Schema::dropIfExists('feature_subtypes');
    }
};

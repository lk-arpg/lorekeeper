<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoundingToCriteria extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('criteria', function (Blueprint $table) {
            $table->enum('rounding', ['No Rounding', 'Traditional Rounding', 'Always Rounds Up', 'Always Rounds Down'])->default('No Rounding');
            $table->integer('round_precision')->unsigned()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('criteria', function (Blueprint $table) {
            $table->dropcolumn('rounding');
            $table->dropcolumn('round_precision');
        });
    }
}

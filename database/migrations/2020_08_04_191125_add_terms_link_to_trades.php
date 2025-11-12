<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTermsLinkToTrades extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('trades', function (Blueprint $table) {
            // Add a column to the trades table for storing link to proof of terms.
            $table->string('terms_link', 200)->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('trades', function (Blueprint $table) {
            //
            $table->dropColumn('terms_link');
        });
    }
}

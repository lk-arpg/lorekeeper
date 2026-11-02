<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('limits', function (Blueprint $table) {
            //
            $table->boolean('is_auto_unlocked')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('limits', function (Blueprint $table) {
            //
            $table->dropColumn('is_auto_unlocked');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('shops', function (Blueprint $table) {
            //
            $table->json('data')->nullable()->after('end_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('shops', function (Blueprint $table) {
            //
            $table->dropColumn('data');
        });
    }
};

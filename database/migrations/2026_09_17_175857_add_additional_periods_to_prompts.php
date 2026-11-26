<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('prompts', function (Blueprint $table) {
            $table->string('limit_period')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        // enums are weird, can not do change() with an enum column
        DB::statement("ALTER TABLE prompts CHANGE COLUMN limit_period limit_period ENUM('Hour', 'Day', 'Week', 'Month', 'Year') NULL DEFAULT NULL");
    }
};

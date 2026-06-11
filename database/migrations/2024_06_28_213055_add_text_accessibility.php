<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->boolean('site_fonts_disabled')->default(0);
            $table->decimal('font_size', 3, 2)->nullable()->default(NULL);
            $table->decimal('letter_spacing', 3, 2)->nullable()->default(NULL);
            $table->decimal('word_spacing', 3, 2)->nullable()->default(NULL);
            $table->decimal('line_height', 3, 2)->nullable()->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->dropColumn('site_fonts_disabled');
            $table->dropColumn('font_size');
            $table->dropColumn('letter_spacing');
            $table->dropColumn('word_spacing');
            $table->dropColumn('line_height');
        });
    }
};

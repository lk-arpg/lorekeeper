<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImagesToSalesNewsPages extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('news', function (Blueprint $table) {
            $table->boolean('has_image')->default(0);
            $table->string('hash', 10)->nullable();
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->boolean('has_image')->default(0);
            $table->string('hash', 10)->nullable();
        });
        Schema::table('site_pages', function (Blueprint $table) {
            $table->boolean('has_image')->default(0);
            $table->string('hash', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn('has_image');
            $table->dropColumn('hash');
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('has_image');
            $table->dropColumn('hash');
        });
        Schema::table('site_pages', function (Blueprint $table) {
            $table->dropColumn('has_image');
            $table->dropColumn('hash');
        });
    }
}

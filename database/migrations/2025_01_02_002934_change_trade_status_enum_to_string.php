<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('trades', function (Blueprint $table) {
            $table->string('status')->default('Open')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        //   `status` enum('Open','Pending','Completed','Rejected','Canceled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Open',
        Schema::table('trades', function (Blueprint $table) {
            $table->enum('status', ['Open', 'Pending', 'Completed', 'Rejected', 'Canceled'])->change();
        });
    }
};

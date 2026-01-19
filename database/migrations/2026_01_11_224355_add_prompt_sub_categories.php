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
        Schema::table('prompt_categories', function (Blueprint $table) {
            $table->integer('parent_id')->default(null)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        //
    }
};

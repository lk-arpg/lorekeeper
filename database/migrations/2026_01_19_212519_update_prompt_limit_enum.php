<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        // make the enum a text instead
        Schema::table('prompts', function ($table) {
            $table->text('limit_period')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        // enum not supported going back
    }
};

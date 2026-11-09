<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        if (!Schema::hasColumn('raffles', 'description')) {
            Schema::table('raffles', function (Blueprint $table) {
                //
                $table->text('description')->nullable()->default(null);
            });
        }

        if (!Schema::hasColumn('raffles', 'parsed_description')) {
            Schema::table('raffles', function (Blueprint $table) {
                //
                $table->text('parsed_description')->nullable()->default(null);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('raffles', function (Blueprint $table) {
            //
            $table->dropColumn('description');
            $table->dropColumn('parsed_description');
        });
    }
};

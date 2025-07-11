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
        if (!Schema::hasTable('character_image_subtypes')) {
            Schema::create('character_image_subtypes', function (Blueprint $table) {
                $table->integer('character_image_id')->unsigned();
                $table->integer('subtype_id')->unsigned();
            });

            Schema::table('design_updates', function (Blueprint $table) {
                $table->string('subtype_ids')->nullable()->default(null);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        //
        Schema::dropIfExists('character_image_subtypes');

        Schema::table('design_updates', function (Blueprint $table) {
            $table->dropColumn('subtype_ids');
        });

        if (!Schema::hasColumn('design_updates', 'subtype_id')) {
            Schema::table('design_updates', function (Blueprint $table) {
                $table->integer('subtype_id')->unsigned()->nullable()->default(null);
            });
        }
    }
};

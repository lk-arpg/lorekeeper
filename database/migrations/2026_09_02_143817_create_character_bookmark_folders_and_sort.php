<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('character_bookmark_folders', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned();
            $table->string('name', 50);
            $table->integer('sort')->unsigned()->default(0);
            $table->index(['user_id', 'sort']);
        });

        Schema::table('character_bookmarks', function (Blueprint $table) {
            $table->integer('folder_id')->unsigned()->nullable()->default(null);
            $table->integer('sort')->unsigned()->default(0);
            $table->index(['user_id', 'folder_id', 'sort']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('character_bookmarks', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'folder_id', 'sort']);
            $table->dropColumn('folder_id');
            $table->dropColumn('sort');
        });

        Schema::dropIfExists('character_bookmark_folders');
    }
};

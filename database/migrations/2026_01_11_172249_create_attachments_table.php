<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->string('parent_model');
            $table->unsignedBigInteger('parent_id');
            $table->string('attachment_type');
            $table->unsignedBigInteger('attachment_id');

            $table->json('data')->nullable()->default(null); // for extensible data storage / extra functionality
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('attachments');
    }
};

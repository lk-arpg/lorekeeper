<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTradeListingsTable extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::create('trade_listings', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            // User that created the trade listing.
            $table->integer('user_id')->unsigned()->index();
            $table->text('comments')->nullable()->default(null);
            // Info about prefered method of contact.
            $table->text('contact')->nullable()->default(null);

            // Information including requested & offered items, characters, currencies, and any other goods/services.
            $table->string('data', 1024)->nullable()->default(null);

            // Timestamps, including for when the trade expires.
            // Only listings whose expiry dates are in the future will be displayed.
            $table->timestamp('expires_at')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::dropIfExists('trade_listings');
    }
}

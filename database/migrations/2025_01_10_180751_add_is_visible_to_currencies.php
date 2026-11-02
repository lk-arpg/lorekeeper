
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsVisibleToCurrencies extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('currencies', function (Blueprint $table) {
            $table->boolean('is_visible')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('currencies', function (Blueprint $table) {
            $table->dropColumn('is_visible');
        });
    }
}

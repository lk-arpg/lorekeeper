<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('shop_stock_costs', function (Blueprint $table) {
            $table->integer('shop_stock_id')->unsigned();
            $table->string('cost_type');
            $table->integer('cost_id')->unsigned();
            $table->integer('quantity');

            $table->json('group')->nullable()->default(null);
        });

        // convert all of the existing costs to the new table
        $stocks = DB::table('shop_stock')->get();
        foreach ($stocks as $stock) {
            DB::table('shop_stock_costs')->insert([
                'shop_stock_id' => $stock->id,
                'cost_type'     => 'Currency',
                'cost_id'       => $stock->currency_id,
                'quantity'      => $stock->cost,
            ]);
        }

        Schema::table('shop_stock', function (Blueprint $table) {
            $table->dropColumn('cost');
            $table->dropColumn('currency_id');

            $table->json('data')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('shop_stock', function (Blueprint $table) {
            $table->integer('currency_id')->unsigned();
            $table->integer('cost');

            $table->dropColumn('data');
        });

        Schema::dropIfExists('shop_stock_costs');
    }
};

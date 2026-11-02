<?php

use App\Models\Currency\Currency;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('shop_log', function (Blueprint $table) {
            $table->string('stock_type')->default('Item');
            $table->json('costs')->nullable()->default(null);
        });

        // convert existing costs and then drop currency_id table
        $logs = DB::table('shop_log')->get();
        foreach ($logs as $log) {
            $assets = createAssetsArray(false);
            addAsset($assets, Currency::find($log->currency_id), $log->cost);
            DB::table('shop_log')->where('id', $log->id)->update([
                'costs' => $log->character_id ? ['character' => getDataReadyAssets($assets)] : ['user' => getDataReadyAssets($assets)],
            ]);
        }

        Schema::table('shop_log', function (Blueprint $table) {
            $table->dropColumn('currency_id');
            // drop cost, then change costs to cost
            $table->dropColumn('cost');
            $table->renameColumn('costs', 'cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        // not reversible
    }
};

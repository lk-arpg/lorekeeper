<?php

namespace App\Console\Commands;

use App\Models\Limit\Limit;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class ConvertShopLimits extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert-shop-limits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Converts existing shop limits to the new system.';

    /**
     * Execute the console command.
     */
    public function handle() {
        if (!Schema::hasTable('shop_limits')) {
            $this->info('No shop limits to convert.');

            return;
        }

        $shopLimits = DB::table('shop_limits')->get();
        $bar = $this->output->createProgressBar(count($shopLimits));
        $bar->start();
        foreach ($shopLimits as $shopLimit) {
            Limit::create([
                'object_model' => 'App\Models\Shop\Shop',
                'object_id'    => $shopLimit->shop_id,
                'limit_type'   => 'item',
                'limit_id'     => $shopLimit->item_id,
                'quantity'     => 1,
            ]);

            $bar->advance();
        }
        $bar->finish();

        // drop the is_restricted column from the shops table
        Schema::table('shops', function ($table) {
            $table->dropColumn('is_restricted');
        });

        Schema::dropIfExists('shop_limits');
    }
}

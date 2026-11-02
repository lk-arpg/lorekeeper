<?php

namespace App\Console\Commands;

use App\Models\Shop\ShopStock;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RestockShops extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restock-shops';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restocks shops.';

    /**
     * Create a new command instance.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
        $stocks = ShopStock::where('is_limited_stock', 1)->where('restock', 1)->get();
        foreach ($stocks as $stock) {
            if ($stock->restock_interval == 2) {
                // check if it's start of week
                $now = Carbon::now();
                $day = $now->dayOfWeek;
                if ($day != 1) {
                    continue;
                }
            } elseif ($stock->restock_interval == 3) {
                // check if it's start of month
                $now = Carbon::now();
                $day = $now->day;
                if ($day != 1) {
                    continue;
                }
            }

            // if the stock is random, restock from the stock type
            if ($stock->isRandom) {
                $type = $stock->stock_type;
                $model = getAssetModelString(strtolower($type));
                if (method_exists($model, 'visible')) {
                    $itemId = $stock->categoryId ?
                        $model::visible()->where(strtolower($type).'_category_id', $stock->categoryId)->inRandomOrder()->first()->id :
                        $model::visible()->inRandomOrder()->first()->id;
                } elseif (method_exists($model, 'released')) {
                    $itemId = $stock->categoryId ?
                        $model::released()->where(strtolower($type).'_category_id', $stock->categoryId)->inRandomOrder()->first()->id :
                        $model::released()->inRandomOrder()->first()->id;
                } else {
                    $itemId = $stock->categoryId ?
                        $model::where(strtolower($type).'_category_id', $stock->categoryId)->inRandomOrder()->first()->id :
                        $model::inRandomOrder()->first()->id;
                }

                $stock->item_id = $itemId;
                $stock->save();
            }

            $stock->quantity = $stock->range ? mt_rand(1, $stock->restock_quantity) : $stock->restock_quantity;
            $stock->save();
        }
    }
}

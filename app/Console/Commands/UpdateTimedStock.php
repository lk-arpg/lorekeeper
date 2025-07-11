<?php

namespace App\Console\Commands;

use App\Models\Shop\Shop;
use App\Models\Shop\ShopStock;
use Illuminate\Console\Command;

class UpdateTimedStock extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-timed-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hides timed stock or shops when expired, or sets it active if ready.';

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
        $hidestock = ShopStock::where('is_timed_stock', 1)->where('is_visible', 1)->get()->filter(function ($stock) {
            return !$stock->isActive;
        });
        $showstock = ShopStock::where('is_timed_stock', 1)->where('is_visible', 0)->get()->filter(function ($stock) {
            return $stock->isActive;
        });

        // set stock that should be active to active
        foreach ($showstock as $showstock) {
            $showstock->is_visible = 1;
            $showstock->save();
        }
        // hide stock that should be hidden now
        foreach ($hidestock as $hidestock) {
            $hidestock->is_visible = 0;
            $hidestock->save();
        }

        // also activate or deactivate the shops
        $hideshop = Shop::where('is_timed_shop', 1)->where('is_active', 1)->get()->filter(function ($shop) {
            return !$shop->isActive;
        });
        $showshop = Shop::where('is_timed_shop', 1)->where('is_active', 0)->get()->filter(function ($shop) {
            return $shop->isActive;
        });

        // set shop that should be active to active
        foreach ($showshop as $showshop) {
            $showshop->is_active = 1;
            $showshop->save();
        }
        // hide shop that should be hidden now
        foreach ($hideshop as $hideshop) {
            $hideshop->is_active = 0;
            $hideshop->save();
        }
    }
}

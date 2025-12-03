<?php

namespace App\Console\Commands;

use App\Models\Shop\ShopStockCost;
use Illuminate\Console\Command;

class FixDeletedShopCosts extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix-deleted-shop-costs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes shop costs in the database orphaned by deleted stock.';

    /**
     * Create a new command instance.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $costs = ShopStockCost::doesntHave('stock');

        $this->info('Deleting '.$costs->count().' orphaned shop costs...');

        $costs->delete();

        $this->info('Success!');
    }
}

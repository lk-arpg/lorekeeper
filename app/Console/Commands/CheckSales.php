<?php

namespace App\Console\Commands;

use App\Services\SalesService;
use Illuminate\Console\Command;

class CheckSales extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check-sales';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks if there are any sales posts to update.';

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
        //
        (new SalesService)->updateQueue();

        return Command::SUCCESS;
    }
}

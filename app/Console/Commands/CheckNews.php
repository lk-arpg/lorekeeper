<?php

namespace App\Console\Commands;

use App\Services\NewsService;
use Illuminate\Console\Command;

class CheckNews extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check-news';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks if there are any news posts to update.';

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
        (new NewsService)->updateQueue();

        return Command::SUCCESS;
    }
}

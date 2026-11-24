<?php

namespace App\Console\Commands;

use App\Services\ExtensionService;
use Illuminate\Console\Command;

class FixCharItemNotifs extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix-char-item-notifs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adjusts character item notification type IDs in the database.';

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
        (new ExtensionService)->updateNotifications(39, 501);
        (new ExtensionService)->updateNotifications(40, 502);

        return Command::SUCCESS;
    }
}

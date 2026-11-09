<?php

namespace App\Console\Commands;

use App\Models\Character\CharacterDesignUpdate;
use Illuminate\Console\Command;

class FixDeletedDesignUpdates extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix-deleted-design-updates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes features and creators orphaned by deleted design updates.';

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
        $deletedUpdates = CharacterDesignUpdate::onlyTrashed()->get();

        foreach ($deletedUpdates as $update) {
            if ($update->rawFeatures->count()) {
                $update->rawFeatures()->delete();
                $this->info('Update #'.$update->id.': Deleted orphaned features');
            }
            if ($update->artists->count()) {
                $update->artists()->delete();
                $this->info('Update #'.$update->id.': Deleted orphaned artists');
            }
            if ($update->designers->count()) {
                $update->designers()->delete();
                $this->info('Update #'.$update->id.': Deleted orphaned designers');
            }
        }

        $this->info('Success!');
    }
}

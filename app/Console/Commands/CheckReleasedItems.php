<?php

namespace App\Console\Commands;

use App\Models\Item\Item;
use App\Models\User\UserItem;
use Illuminate\Console\Command;

class CheckReleasedItems extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-released-items';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks unreleased items to see if they are or have been owned by at least one user, and updates them if so.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
        $userItems = UserItem::pluck('item_id')->unique()->toArray();
        $releasedItems = Item::where('is_released', 0)->whereIn('id', $userItems);

        if ($releasedItems->count()) {
            $this->line('Updating items...');
            $releasedItems->update(['is_released' => 1]);
            $this->info('Updated items.');
        } else {
            $this->line('No items need updating!');
        }

        return Command::SUCCESS;
    }
}

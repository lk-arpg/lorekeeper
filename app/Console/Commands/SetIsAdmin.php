<?php

namespace App\Console\Commands;

use App\Models\Rank\Rank;
use Illuminate\Console\Command;

class SetIsAdmin extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:set-is-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets the "is admin" property for existing ranks.';

    /**
     * Execute the console command.
     */
    public function handle() {
        $adminRank = Rank::orderBy('sort', 'DESC')->first();

        $adminRank->update([
            'is_admin' => 1,
        ]);
    }
}

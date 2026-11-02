<?php

namespace App\Console\Commands;

use App\Models\Loot\Loot;
use Illuminate\Console\Command;

class FixEqualsCriteriaLoots extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix-equals-criteria-loots';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix equals criteria loots to double equals for use in where clause';

    /**
     * Execute the console command.
     */
    public function handle() {
        //
        $loots = Loot::whereNotNull('data')->get();

        foreach ($loots as $loot) {
            $data = $loot->data;

            if (isset($data['criteria']) && $data['criteria'] == '=') {
                $data['criteria'] = '==';

                Loot::where([
                    ['loot_table_id', '=', $loot->loot_table_id],
                    ['rewardable_type', '=', $loot->rewardable_type],
                    ['rewardable_id', '=', $loot->rewardable_id],
                ])->update(['data' => $data]);
            }
        }
    }
}

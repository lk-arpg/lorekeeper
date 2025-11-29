<?php

namespace App\Console\Commands;

use App\Models\Reward\Reward;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class ConvertRewards extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert-rewards';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Converts existing rewards on general objects (ex. prompts) to the new system.';

    /**
     * Execute the console command.
     */
    public function handle() {
        $this->info('************************');
        $this->info('* CONVERT REWARDS     *');
        $this->info('************************'."\n");

        $this->line("Converting prompt rewards...\n");
        if (Schema::hasTable('prompt_rewards')) {
            $promptRewards = DB::table('prompt_rewards')->get();
            $bar = $this->output->createProgressBar(count($promptRewards));
            $bar->start();
            foreach ($promptRewards as $promptReward) {
                Reward::create([
                    'object_model'    => 'App\Models\Prompt\Prompt',
                    'object_id'       => $promptReward->prompt_id,
                    'rewardable_type' => $promptReward->rewardable_type,
                    'rewardable_id'   => $promptReward->rewardable_id,
                    'quantity'        => $promptReward->quantity,
                ]);

                $bar->advance();
            }
            $bar->finish();
            $this->info("\nDone!");
            Schema::dropIfExists('prompt_rewards');
        } else {
            $this->info('No prompt rewards to convert.');
        }

        // Add other object types here as needed...

        $this->info("\nAll done!");
    }
}

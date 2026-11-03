<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateStaffRewardActions extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-staff-reward-actions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run this command in order to add staff actions with configurable rewards.';

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
        $this->info("\n".'*******************************');
        $this->info('* UPDATE STAFF REWARD ACTIONS *');
        $this->info('*******************************');

        $actions = [];
        foreach (glob('config/lorekeeper/staff-reward-actions/*.php') as $action) {
            $actions[basename($action, '.php')] = include $action;
        }

        $this->line('Adding staff actions...existing entries will be skipped.'."\n");

        foreach ($actions as $key => $data) {
            $action = DB::table('staff_actions')->where('key', $key);
            if (!$action->exists()) {
                DB::table('staff_actions')->insert([
                    'key'         => $key,
                    'name'        => $data['name'],
                    'description' => $data['description'],
                    'value'       => 1,
                ]);
                $this->info('Added:   '.$key);
            } else {
                $this->line('Skipped: '.$key);
            }
        }

        if (app()->runningInConsole()) {
            $processed = array_keys($actions);
            $actions = DB::table('staff_actions')->pluck('key')->toArray();

            $missing = array_merge(array_diff($processed, $actions), array_diff($actions, $processed));

            if (count($missing)) {
                $miss = implode(', ', $missing);
                $this->line("\033[31m");
                $this->error('The following action'.(count($missing) == 1 ? ' is' : 's are').' not present as a file but '.(count($missing) == 1 ? 'is' : 'are').' still in your database:');
                $this->line($miss);

                $confirm = $this->confirm('Do you want to remove '.(count($missing) == 1 ? 'this action' : 'these actions').' from your database and actions list? This will not affect any other files.');
                if ($confirm) {
                    foreach ($missing as $act) {
                        $action = DB::table('staff_actions')->where('key', $act)->delete();
                        $this->info('Deleted:   '.$act);
                    }
                } else {
                    $this->line('Leaving action'.(count($missing) == 1 ? '' : 's').' alone.');
                }
            }
        }

        $this->info("\n".'All actions have been added.'."\n");

        return Command::SUCCESS;
    }
}

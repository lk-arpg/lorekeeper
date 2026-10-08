<?php

namespace App\Console\Commands;

use App\Models\User\User;
use Illuminate\Console\Command;

class FixRegisteredUsers extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix-registered-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes users who registered during fortify setup and did not have a user_setting or user_profile entry created.';

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
        // get all users without a user_setting and user_profile entry
        $users = User::whereDoesntHave('settings')->whereDoesntHave('profile')->get();
        foreach ($users as $user) {
            $this->line('Creating user settings and profile for user '.$user->name.'...');
            $user->settings()->create([
                'user_id' => $user->id,
            ]);
            $user->profile()->create([
                'user_id' => $user->id,
            ]);
        }

        return Command::SUCCESS;
    }
}

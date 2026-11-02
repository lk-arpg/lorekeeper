<?php

namespace App\Console\Commands;

use App;
use App\Models\Rank\Rank;
use App\Models\User\User;
use App\Models\User\UserAlias;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SetupAdminUser extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup-admin-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates the admin user account if no users exist, or resets the password if it does.';

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
        $this->info('********************');
        $this->info('* ADMIN USER SETUP *');
        $this->info('********************'."\n");

        // First things first, check if user ranks exist...
        if (!Rank::count()) {
            // These need to be created even if the seeder isn't run for the site to work correctly.
            $adminRank = Rank::create([
                'name'               => 'Admin',
                'description'        => 'The site admin. Has the ability to view/edit any data on the site.',
                'parsed_description' => 'The site admin. Has the ability to view/edit any data on the site.',
                'sort'               => 1,
                'is_admin'           => 1,
            ]);
            Rank::create([
                'name'               => 'Member',
                'description'        => 'A regular member of the site.',
                'parsed_description' => 'A regular member of the site.',
                'sort'               => 0,
            ]);

            $this->line('User ranks not found. Default user ranks (admin and basic member) created.');
        }
        // Otherwise, grab the rank with the highest "sort" value. (This is the admin rank.)
        else {
            $adminRank = Rank::orderBy('sort', 'DESC')->first();
        }

        // Check if the admin user exists...
        $user = User::where('rank_id', $adminRank->id)->first();
        if (!$user) {
            $this->line('Setting up admin account. This account will have access to all site data, please make sure to keep the email and password secret!');
            $name = $this->anticipate('Username', ['Admin', 'System']);
            $email = $this->ask('Email Address');
            $password = $this->secret('Password (hidden)');

            $this->line("\nUsername: ".$name);
            $this->line('Email: '.$email);
            $confirm = $this->confirm('Proceed to create account with this information?');

            // If env variables indicate a local instance, double-check
            // and then if so, help them set up verified email and alias
            if (App::environment('local')) {
                if ($this->confirm('Are you on a local/testing instance and not a live site?')) {
                    if ($this->confirm('Would you like to mark your email address as verified and enter an alias now?')) {
                        $verifiedAt = Carbon::now();
                        $this->line('Provide an alias. By default, this will be entered as a deviantArt account name, but (assuming a local/testing environment) you may use any username.');
                        $alias = $this->ask('What alias would you like to use?');
                    }
                } else {
                    $this->info('Please adjust your APP_ENV to Production and APP_DEBUG to false in your .env file before continuing set-up!');

                    return;
                }
            }

            if ($confirm) {
                $service = new UserService;
                $user = $service->createUser([
                    'name'      => $name,
                    'email'     => $email,
                    'rank_id'   => $adminRank->id,
                    'password'  => $password,
                    'dob'       => Carbon::createFromFormat('Y-m-d', '1970-01-01'),
                    'has_alias' => isset($alias) ? 1 : 0,
                ]);
                if (isset($verifiedAt)) {
                    $user->email_verified_at = $verifiedAt;
                    $user->save();
                }

                if (isset($alias)) {
                    UserAlias::create([
                        'user_id'          => $user->id,
                        'site'             => 'deviantart',
                        'alias'            => $alias,
                        'is_primary_alias' => 1,
                        'is_visible'       => 1,
                    ]);
                }

                $this->line('Admin account created. You can now log in with the registered email and password.');
                $this->line('If necessary, you can run this command again to change the email address and password of the admin account.');

                return;
            }
        } else {
            // Change the admin email/password. Honestly you can do this with the forgotten password feature...
            $this->line('Admin account ['.$user->name.'] already exists.');
            if ($this->confirm('Reset email address and password for this account?')) {
                $email = $this->ask('Email Address');
                $password = $this->secret('Password (hidden)');

                $this->line("\nEmail: ".$email);
                if ($this->confirm('Proceed to change email address and password?')) {
                    $service = new UserService;
                    $service->updateUser([
                        'id'       => $user->id,
                        'email'    => $email,
                        'password' => $password,
                    ]);

                    $this->line('Admin account email and password changed.');
                }
            }

            // If env variables indicate a local instance, double-check
            // and then if so, help them set up verified email and alias
            if (App::environment('local')) {
                if ($this->confirm('Are you on a local/testing instance and not a live site?')) {
                    if ($this->confirm('Would you like to mark your email address as verified?')) {
                        $verifiedAt = Carbon::now();
                    }
                    if (!$user->has_alias && $this->confirm('Would you like to enter an alias now?')) {
                        $this->line('Provide an alias. By default, this will be entered as a deviantArt account name, but (assuming a local/testing environment) you may use any username.');
                        $alias = $this->ask('What alias would you like to use?');
                    }
                } else {
                    $this->info('Please adjust your APP_ENV to Production and APP_DEBUG to false in your .env file before continuing set-up!');

                    return;
                }

                if (isset($verifiedAt)) {
                    $this->line('Marking email address as verified...');
                    $user->email_verified_at = $verifiedAt;
                    $user->save();
                }

                if (isset($alias)) {
                    $this->line('Adding user alias...');
                    $user->update(['has_alias' => 1]);
                    UserAlias::create([
                        'user_id'          => $user->id,
                        'site'             => 'deviantart',
                        'alias'            => $alias,
                        'is_primary_alias' => 1,
                        'is_visible'       => 1,
                    ]);
                }

                $this->line('Updates complete.');
            }

            return;
        }
        $this->line('Action cancelled.');
    }
}

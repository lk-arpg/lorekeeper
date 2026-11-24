<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateLorekeeperV3 extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-lorekeeper-v3';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs commands to update Lorekeeper to version 3.0 from version 2.';

    /**
     * Create a new command instance.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
        $this->info('**************************');
        $this->info('* UPDATE LOREKEEPER (V3) *');
        $this->info('**************************'."\n");

        // Check if the user has run composer
        $this->info('This command should be run after installing packages using composer.');
        if ($this->confirm('Have you run the composer install command or equivalent?')) {
            // Migrate
            $this->line('Running migrations...');
            $this->call('migrate');

            // Clear caches
            $this->line("\n".'Clearing caches...');
            $this->call('optimize');
            $this->call('view:clear');
            $this->call('route:clear');

            // Run miscellaneous commands
            $this->line("\n".'Updating site pages and settings...');
            $this->call('add-site-settings');
            $this->call('add-text-pages');

            $this->line("\n".'Updating character images...');
            $this->call('app:fill-character-fullsize-extensions');

            $this->line("\n".'Updating released items...');
            $this->call('update-released-items');

            $this->line("\n".'Updating comments...');
            $this->call('update-comment-types');

            if ($this->confirm(
                "\n".'Adding image hashes to old images will protect existing unreleased content'
                ."\n".'but may break references to these urls in places like news, sales or pages.'
                ."\n".'Do you wish to add hashes to your existing images?'
            )) {
                $this->line("\n".'Updating data images...');
                $this->call('add-image-hashes');
            }
        } else {
            $this->line('Aborting! Please run composer update and then run this command again.');
        }

        return Command::SUCCESS;
    }
}

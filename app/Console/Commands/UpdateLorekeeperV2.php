<?php

namespace App\Console\Commands;

use App\Models\Comment\Comment;
use Illuminate\Console\Command;

class UpdateLorekeeperV2 extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-lorekeeper-v2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs commands to update Lorekeeper to version 2.0 from version 1.';

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
        $this->info('* UPDATE LOREKEEPER (V2) *');
        $this->info('**************************'."\n");

        // Check if the user has run composer
        $this->info('This command should be run after updating packages using composer.');
        if ($this->confirm('Have you run the composer update command or equivalent?')) {
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

            $this->line("\n".'Updating comments...');
            // Folding in fix-child-comment-types for tidiness
            $childComments = Comment::whereNotNull('child_id')->get();
            if ($childComments->count()) {
                $this->line('Updating '.$childComments->count().' child comments...');
                foreach ($childComments as $comment) {
                    $parent = Comment::find($comment->child_id);
                    if (isset($parent->type) && $comment->type != $parent->type) {
                        $comment->update(['type' => $parent->type]);
                    }
                }
                $this->info('Child comments updated!');
            } else {
                $this->line('No child comments to update!');
            }

            // Folding in update-sales-comments for tidiness
            $salesComments = Comment::where('commentable_type', 'App\Models\Sales')->get();
            if ($salesComments->count()) {
                $this->line('Updating '.$salesComments->count().' sales comments...');
                foreach ($salesComments as $comment) {
                    $comment->commentable_type = 'App\Models\Sales\Sales';
                    $comment->save();
                }
                $this->info('Sales comments updated!');
            } else {
                $this->line('No sales comments to update!');
            }

            // Migrate aliases
            $this->line("\n".'Updating alias information...');
            $this->call('migrate-aliases');
        } else {
            $this->line('Aborting! Please run composer update and then run this command again.');
        }

        return Command::SUCCESS;
    }
}

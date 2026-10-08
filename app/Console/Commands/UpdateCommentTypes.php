<?php

namespace App\Console\Commands;

use App\Models\Comment\Comment;
use Illuminate\Console\Command;

class UpdateCommentTypes extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-comment-types';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates comment types.';

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
        $comments = Comment::where('commentable_type', 'App\Models\Report\Report')->where('type', 'User-User');

        if ($comments->count()) {
            $this->line('Updating comment types...');
            $comments->update(['type' => 'Staff-User']);
            $this->info('Comment types updated!');
        } else {
            $this->line('No comments need updating!');
        }

        return Command::SUCCESS;
    }
}

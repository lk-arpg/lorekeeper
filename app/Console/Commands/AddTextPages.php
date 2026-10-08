<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddTextPages extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add-text-pages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds the default text pages that are present on every site.';

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
        //
        $pages = config('lorekeeper.text_pages');

        $this->info('******************');
        $this->info('* ADD SITE PAGES *');
        $this->info('******************'."\n");

        $this->line("Adding site pages...existing entries will be skipped.\n");

        foreach ($pages as $key => $page) {
            if (!DB::table('site_pages')->where('key', $key)->exists()) {
                DB::table('site_pages')->insert([
                    [
                        'key'         => $key,
                        'title'       => $page['title'],
                        'text'        => $page['text'],
                        'parsed_text' => $page['text'],
                        'created_at'  => Carbon::now(),
                        'updated_at'  => Carbon::now(),
                    ],

                ]);
                $this->info('Added:   '.$page['title']);
            } else {
                $this->line('Skipped: '.$page['title']);
            }
        }

        return Command::SUCCESS;
    }
}

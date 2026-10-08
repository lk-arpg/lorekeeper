<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateExtensionTracker extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-extension-tracker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run this command in order to add extensions to your list of extensions. This helps you to keep track of what extensions are used on your site and who created them.';

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
        $this->info("\n".'****************************');
        $this->info('* UPDATE EXTENSION TRACKER *');
        $this->info('****************************');

        $extendos = [];
        foreach (glob('config/lorekeeper/ext-tracker/*.php') as $extension) {
            $extendos[basename($extension, '.php')] = include $extension;
        }

        $this->line('Adding site extensions...existing entries will be updated.'."\n");

        foreach ($extendos as $key => $data) {
            $extension = DB::table('site_extensions')->where('key', $key);
            if (!$extension->exists()) {
                DB::table('site_extensions')->insert([
                    'key'      => $key,
                    'wiki_key' => $data['wiki_key'],
                    'creators' => $data['creators'],
                    'version'  => $data['version'],
                ]);
                $this->info('Added:   '.$key.' / Version: '.$data['version']);
            } elseif ($extension->first()->version != $data['version']) {
                $this->info(ucfirst($key).' version mismatch. Old version: '.$extension->first()->version.' / New version: '.$data['version']);
                $confirm = $this->confirm('Do you want to update the listed version of '.$key.' to '.$data['version'].'? This will not affect any other files.');
                if ($confirm || !app()->runningInConsole()) {
                    DB::table('site_extensions')->where('key', $key)->update([
                        'key'      => $key,
                        'wiki_key' => $data['wiki_key'],
                        'creators' => $data['creators'],
                        'version'  => $data['version'],
                    ]);
                    $this->info('Updated:   '.$key.' / Version: '.$data['version']);
                } else {
                    $this->line('Skipped: '.$key.' / Version: '.$extension->first()->version);
                }
            } else {
                $this->line('Skipped: '.$key.' / Version: '.$data['version']);
            }
        }

        if (app()->runningInConsole()) {
            $extensions = DB::table('site_extensions')->pluck('key')->toArray();
            $processed = array_keys($extendos);

            $missing = array_merge(array_diff($processed, $extensions), array_diff($extensions, $processed));

            if (count($missing)) {
                $miss = implode(', ', $missing);
                $this->line("\033[31m");
                $this->error('The following extension'.(count($missing) == 1 ? ' is' : 's are').' not present as a file but '.(count($missing) == 1 ? 'is' : 'are').' still in your database:');
                $this->line($miss);

                $confirm = $this->confirm('Do you want to remove '.(count($missing) == 1 ? 'this extension' : 'these extensions').' from your database and extensions list? This will not affect any other files.');
                if ($confirm) {
                    foreach ($missing as $ext) {
                        $extension = DB::table('site_extensions')->where('key', $ext)->delete();
                        $this->info('Deleted:   '.$ext);
                    }
                } else {
                    $this->line('Leaving extension'.(count($missing) == 1 ? '' : 's').' alone.');
                }
            }
        }

        $this->info("\n".'All extensions are in tracker.'."\n");

        return Command::SUCCESS;
    }
}

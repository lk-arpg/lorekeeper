<?php

namespace App\Console\Commands;

use App\Models\Feature\Feature;
use App\Models\Feature\FeatureSubtype;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ConvertTraitSubtype extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert-trait-subtype';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Converts the subtype_id column in the features table to a new row in the feature_subtypes table.';

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
        if (Schema::hasTable('feature_subtypes')) {
            if (!Schema::hasColumn('features', 'subtype_id')) {
                $this->info('This command will not execute, as it has already been run.');

                return;
            }

            $features = Feature::where(function ($query) {
                $query->whereNotNull('subtype_id');
            })->get();

            $this->info('Converting '.count($features).' features\' subtypes...');
            $bar = $this->output->createProgressBar(count($features));
            foreach ($features as $feature) {
                if ($feature->subtype_id) {
                    FeatureSubtype::create([
                        'feature_id' => $feature->id,
                        'subtype_id' => $feature->subtype_id,
                    ]);
                }
                $bar->advance();
            }

            $bar->finish();
            $this->info("\n".'Dropping subtype ID column from the feature table...');

            Schema::table('features', function (Blueprint $table) {
                $table->dropColumn('subtype_id');
            });

            $this->info('Done!');
        } else {
            $this->info('Required table does not exist, please run migrations first.');
        }
    }
}

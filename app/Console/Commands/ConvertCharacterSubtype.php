<?php

namespace App\Console\Commands;

use App\Models\Character\CharacterDesignUpdate;
use App\Models\Character\CharacterImage;
use App\Models\Character\CharacterImageSubtype;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ConvertCharacterSubtype extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert-character-subtype';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Converts the subtype_id columns in the character_images table to a new row in character_image_subtype table.';

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
        if (Schema::hasColumn('character_images', 'subtype_id')) {
            // Check for second subtype columns up front to save on some redundant checks
            $secondSubtype = false;
            $thirdSubtype = false;
            if (Schema::hasColumn('character_images', 'subtype_id_2') && Schema::hasColumn('design_updates', 'subtype_id_2')) {
                $secondSubtype = true;
                if (Schema::hasColumn('character_images', 'subtype_id_3') && Schema::hasColumn('design_updates', 'subtype_id_3')) {
                    $thirdSubtype = true;
                }
            }

            // DESIGN UPDATES
            $updates = CharacterDesignUpdate::where(function ($query) use ($secondSubtype, $thirdSubtype) {
                if ($thirdSubtype) {
                    $query->whereNotNull('subtype_id')->orWhereNotNull('subtype_id_2')->orWhereNotNull('subtype_id_3');
                } elseif ($secondSubtype) {
                    $query->whereNotNull('subtype_id')->orWhereNotNull('subtype_id_2');
                } else {
                    $query->whereNotNull('subtype_id');
                }
            })->get();

            $this->info('Converting '.count($updates).' updates\' subtypes...');
            $updateBar = $this->output->createProgressBar(count($updates));

            foreach ($updates as $update) {
                $updateSubtypes = [];
                if ($update->subtype_id) {
                    $updateSubtypes[] = $update->subtype_id;
                }
                if ($secondSubtype && $update->subtype_id_2) {
                    $updateSubtypes[] = $update->subtype_id_2;
                }
                if ($thirdSubtype && $update->subtype_id_3) {
                    $updateSubtypes[] = $update->subtype_id_3;
                }

                $update->update([
                    'subtype_ids' => $updateSubtypes,
                ]);
                $updateBar->advance();
            }

            $updateBar->finish();
            $this->info("\n".'Dropping subtype ID column'.($secondSubtype ? 's' : '').' from the design updates table...');

            Schema::table('design_updates', function (Blueprint $table) {
                $table->dropColumn('subtype_id');
            });
            if ($secondSubtype) {
                Schema::table('design_updates', function (Blueprint $table) {
                    $table->dropColumn('subtype_id_2');
                });
            }
            if ($thirdSubtype) {
                Schema::table('design_updates', function (Blueprint $table) {
                    $table->dropColumn('subtype_id_3');
                });
            }

            // CHARACTER IMAGES
            $characterImages = CharacterImage::where(function ($query) use ($secondSubtype, $thirdSubtype) {
                if ($thirdSubtype) {
                    $query->whereNotNull('subtype_id')->orWhereNotNull('subtype_id_2')->orWhereNotNull('subtype_id_3');
                } elseif ($secondSubtype) {
                    $query->whereNotNull('subtype_id')->orWhereNotNull('subtype_id_2');
                } else {
                    $query->whereNotNull('subtype_id');
                }
            })->get();

            $this->info('Converting '.count($characterImages).' character images\' subtypes...');
            $imageBar = $this->output->createProgressBar(count($characterImages));
            foreach ($characterImages as $characterImage) {
                if ($characterImage->subtype_id) {
                    CharacterImageSubtype::create([
                        'character_image_id' => $characterImage->id,
                        'subtype_id'         => $characterImage->subtype_id,
                    ]);
                }

                if ($secondSubtype && $characterImage->subtype_id_2) {
                    CharacterImageSubtype::create([
                        'character_image_id' => $characterImage->id,
                        'subtype_id'         => $characterImage->subtype_id_2,
                    ]);
                }

                if ($thirdSubtype && $characterImage->subtype_id_3) {
                    CharacterImageSubtype::create([
                        'character_image_id' => $characterImage->id,
                        'subtype_id'         => $characterImage->subtype_id_3,
                    ]);
                }
                $imageBar->advance();
            }

            $imageBar->finish();
            $this->info("\n".'Dropping subtype ID column'.($secondSubtype ? 's' : '').' from the character images table...');

            Schema::table('character_images', function (Blueprint $table) {
                $table->dropColumn('subtype_id');
            });
            if ($secondSubtype) {
                Schema::table('character_images', function (Blueprint $table) {
                    $table->dropColumn('subtype_id_2');
                });
            }
            if ($thirdSubtype) {
                Schema::table('character_images', function (Blueprint $table) {
                    $table->dropColumn('subtype_id_3');
                });
            }

            $this->info('Done!');
        } else {
            $this->info('This command will not execute, as it has already been run.');
        }
    }
}

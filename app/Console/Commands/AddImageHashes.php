<?php

namespace App\Console\Commands;

use App\Models\Character\CharacterCategory;
use App\Models\Currency\Currency;
use App\Models\Feature\Feature;
use App\Models\Feature\FeatureCategory;
use App\Models\Item\Item;
use App\Models\Item\ItemCategory;
use App\Models\Prompt\Prompt;
use App\Models\Prompt\PromptCategory;
use App\Models\Rarity;
use App\Models\Shop\Shop;
use App\Models\Species\Species;
use App\Models\Species\Subtype;
use App\Services\FeatureService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AddImageHashes extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add-image-hashes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds hashes to any existing images that don\'t already have them.';

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
        $images = CharacterCategory::where('has_image', 1)->whereNull('hash')->get();
        $images = $images->concat(Currency::where('has_image', 1)->whereNull('hash')->orWhere('has_icon', 1)->whereNull('hash')->get());
        $images = $images->concat(Feature::where('has_image', 1)->whereNull('hash')->get());
        $images = $images->concat(FeatureCategory::where('has_image', 1)->whereNull('hash')->get());
        $images = $images->concat(Item::where('has_image', 1)->whereNull('hash')->get());
        $images = $images->concat(ItemCategory::where('has_image', 1)->whereNull('hash')->get());
        $images = $images->concat(Prompt::where('has_image', 1)->whereNull('hash')->get());
        $images = $images->concat(PromptCategory::where('has_image', 1)->whereNull('hash')->get());
        $images = $images->concat(Rarity::where('has_image', 1)->whereNull('hash')->get());
        $images = $images->concat(Shop::where('has_image', 1)->whereNull('hash')->get());
        $images = $images->concat(Species::where('has_image', 1)->whereNull('hash')->get());
        $images = $images->concat(Subtype::where('has_image', 1)->whereNull('hash')->get());

        if ($images->count()) {
            $this->line('Updating images...');
            foreach ($images as $image) {
                $oldName = $image->id.'-image.png';
                $image->hash = randomString(10);
                // Any service works, I can't use the abstract one
                if (
                    File::exists(public_path($image->imageDirectory).'/'.$oldName) &&
                    (new FeatureService)->handleImage(
                        null,
                        public_path($image->imageDirectory),
                        $image->id.'-'.$image->hash.'-image.png',
                        $oldName
                    )
                ) {
                    $image->save();
                } else {
                    $this->info('Didn\'t add hash to '.get_class($image).', this could be expected or an error, id '.$image->id);
                }

                // Just for currency icons
                if ($image instanceof Currency) {
                    $oldName = $image->id.'-icon.png';
                    if (
                        File::exists(public_path($image->imageDirectory).'/'.$oldName) &&
                        (new FeatureService)->handleImage(
                            null,
                            public_path($image->imageDirectory),
                            $image->id.'-'.$image->hash.'-icon.png',
                            $oldName
                        )
                    ) {
                        $image->save();
                    } else {
                        $this->info('Didn\'t add hash to currency icon image, this could be expected or an error, id '.$image->id);
                    }
                }
            }
            $this->info('Updated images.');
        } else {
            $this->line('No images need updating!');
        }
    }
}

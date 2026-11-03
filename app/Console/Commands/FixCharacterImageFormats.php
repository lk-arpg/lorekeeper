<?php

namespace App\Console\Commands;

use App\Models\Character\CharacterDesignUpdate;
use App\Models\Character\CharacterImage;
use Illuminate\Console\Command;
use Intervention\Image\Facades\Image;

class FixCharacterImageFormats extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-character-image-formats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Converts existing fullsize character and design update images not stored as the configured file format.';

    /**
     * Execute the console command.
     */
    public function handle() {
        $masterlistFormat = config('lorekeeper.settings.masterlist_image_format');
        $fullsizeFormat = config('lorekeeper.settings.masterlist_fullsizes_format');

        $images = CharacterImage::where('fullsize_extension', '!=', $fullsizeFormat);
        if ($images->count()) {
            $this->info('Processing '.$images->count().' images...');
            $images->update(['fullsize_extension' => $fullsizeFormat]);

            foreach ($images->get() as $image) {
                if (file_exists($image->imagePath.'/'.$image->id.'_'.$image->hash.'_'.$image->fullsize_hash.'_full.'.$masterlistFormat)) {
                    Image::make($image->imagePath.'/'.$image->id.'_'.$image->hash.'_'.$image->fullsize_hash.'_full.'.$masterlistFormat)->save($image->imagePath.'/'.$image->fullsizeFileName, 100, $fullsizeFormat);

                    if (file_exists($image->imagePath.'/'.$image->fullsizeFileName)) {
                        unlink($image->imagePath.'/'.$image->id.'_'.$image->hash.'_'.$image->fullsize_hash.'_full.'.$masterlistFormat);
                    }
                }
            }
        }

        $updates = CharacterDesignUpdate::where('status', '!=', 'Approved');
        if ($updates->count()) {
            $this->info('Processing '.$updates->count().' updates...');
            foreach ($updates->get() as $update) {
                $updates->update(['extension' => $fullsizeFormat]);

                if (file_exists($update->imagePath.'/'.$update->id.'_'.$update->hash.'.'.$masterlistFormat)) {
                    Image::make($update->imagePath.'/'.$update->id.'_'.$update->hash.'.'.$masterlistFormat)->save($update->imagePath.'/'.$update->imageFileName, 100, $fullsizeFormat);

                    if (file_exists($update->imagePath.'/'.$update->imageFileName)) {
                        unlink($update->imagePath.'/'.$update->id.'_'.$update->hash.'.'.$masterlistFormat);
                    }
                }

                if (!file_exists($update->imagePath.'/'.$update->thumbnailFileName) && file_exists($update->imagePath.'/'.$update->id.'_'.$update->hash.'_th.'.$fullsizeFormat)) {
                    Image::make($update->imagePath.'/'.$update->id.'_'.$update->hash.'_th.'.$fullsizeFormat)->save($update->imagePath.'/'.$update->thumbnailFileName, 100, $masterlistFormat);

                    if (file_exists($update->imagePath.'/'.$update->thumbnailFileName)) {
                        unlink($update->imagePath.'/'.$update->id.'_'.$update->hash.'_th.'.$fullsizeFormat);
                    }
                }
            }
        }

        return Command::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Character\Character;
use App\Models\Character\CharacterCurrency;
use App\Models\Character\CharacterFeature;
use App\Models\Character\CharacterImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearDeletedCharacterAttachments extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear-deleted-character-attachments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears any currently remaining character attachments (features and currency) so that they can be deleted.';

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
        // Get deleted character IDs
        $deletedCharacterIds = DB::table('characters')->whereNotNull('deleted_at')->pluck('id');

        // Delete their held currencies
        CharacterCurrency::whereIn('character_id', $deletedCharacterIds)->delete();

        // Delete their character images
        CharacterImage::whereIn('character_id', $deletedCharacterIds)->delete();

        // Get all deleted character images
        $deletedImageIds = DB::table('character_images')->whereNotNull('deleted_at')->pluck('id');

        // Delete their features
        CharacterFeature::whereIn('character_image_id', $deletedImageIds)->delete();

        return Command::SUCCESS;
    }
}

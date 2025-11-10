<?php

namespace App\Console\Commands;

use App\Models\Item\Item;
use App\Models\Rarity;
use Illuminate\Console\Command;

class ConvertItemRarities extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert-item-rarities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Converts previously arbitrary item rarities (numeric format) to utilise existing rarity model';

    /**
     * Execute the console command.
     */
    public function handle() {
        //
        if (!config('lorekeeper.extensions.item_entry_expansion.extra_fields')) {
            $this->info('Item Entry Expansion is not enabled. Exiting.');

            return;
        }
        $rarityItems = Item::whereNotNull('data->rarity')->get();
        // pluck the rarity values from the items
        $rarityValues = $rarityItems->pluck('data.rarity')->unique();
        foreach ($rarityValues as $rarityValue) {
            $this->info("\nCreating rarity for value: ".$rarityValue.'...');
            $nearestRarity = Rarity::where('sort', $rarityValue)->first();
            if (!$nearestRarity) {
                $nearestRarity = Rarity::where('sort', $rarityValue - 1)->first();
            }

            if ($nearestRarity) {
                $this->info('Closest rarity (based on sort) found: '.$nearestRarity->name);
                $this->info('If this rarity is not correct, please enter "n" and choose the correct rarity.');
                $ask = $this->ask('Do you want to use this rarity for the value '.$rarityValue.'? (y/n)', 'y');
                if ($ask === 'y') {
                    $rarityItems->where('data.rarity', $rarityValue)->each(function ($item) use ($nearestRarity) {
                        $item->data = array_merge($item->data, ['rarity_id' => $nearestRarity->id]);
                        $item->save();
                    });
                    $this->info('Items with rarity value '.$rarityValue.' have been updated to use rarity '.$nearestRarity->name);
                } else {
                    $rarityName = $this->ask('Enter the name of the rarity you want to use for the value '.$rarityValue);
                    $rarity = Rarity::where('name', 'LIKE', '%'.$rarityName.'%')->first();
                    if ($rarity) {
                        $check = $this->ask('Do you want to use the rarity '.$rarity->name.' for the value '.$rarityValue.'? (y/n)', 'y');
                        if ($check === 'y') {
                            $rarityItems->where('data.rarity', $rarityValue)->each(function ($item) use ($rarity) {
                                $item->data = array_merge($item->data, ['rarity_id' => $rarity->id]);
                                $item->save();
                            });
                            $this->info('Items with rarity value '.$rarityValue.' have been updated to use rarity '.$rarity->name);
                        } else {
                            $this->info('No changes made for rarity value '.$rarityValue.'.');
                        }
                    } else {
                        $this->info('No matching rarity found for name '.$rarityName.'.');
                    }
                }
            } else {
                $this->info('No matching rarity found for value '.$rarityValue.'.');
                $rarityName = $this->ask('Enter the name of the rarity you want to use for the value '.$rarityValue.':');
                $rarity = Rarity::whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($rarityName).'%'])->first();
                if ($rarity) {
                    $check = $this->ask('Do you want to use the rarity '.$rarity->name.' for the value '.$rarityValue.'? (y/n)', 'y');
                    if ($check === 'y') {
                        $rarityItems->where('data.rarity', $rarityValue)->each(function ($item) use ($rarity) {
                            $item->data = array_merge($item->data, ['rarity_id' => $rarity->id]);
                            $item->save();
                        });
                        $this->info('Items with rarity value '.$rarityValue.' have been updated to use rarity '.$rarity->name);
                    } else {
                        $this->info('No changes made for rarity value '.$rarityValue.'.');
                    }
                } else {
                    $this->info('No matching rarity found for name '.$rarityName.'.');
                    $this->info('Feel free to re-run the command to try again.');
                }
            }
        }
    }
}

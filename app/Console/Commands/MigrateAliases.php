<?php

namespace App\Console\Commands;

use App\Models\Character\Character;
use App\Models\Character\CharacterImageCreator;
use App\Models\Character\CharacterLog;
use App\Models\Item\Item;
use App\Models\User\User;
use App\Models\User\UserAlias;
use App\Models\User\UserCharacterLog;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateAliases extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate-aliases {--drop-columns : Whether the alias columns should be dropped after moving data from them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrates alias information associated with users, characters, and character image creators to the new storage system.';

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
        $this->info('*****************************');
        $this->info('* MIGRATE ALIAS INFORMATION *');
        $this->info('*****************************'."\n");

        $this->line("Migrating aliases...\n");

        /* MOVE USER ALIASES */
        if (Schema::hasColumn('users', 'alias')) {
            // Get users with a set alias
            $aliasUsers = User::whereNotNull('alias')->get();

            if ($aliasUsers->count()) {
                foreach ($aliasUsers as $user) {
                    if (!DB::table('user_aliases')->where('user_id', $user->id)->where('site', 'deviantart')->where('alias', $user->alias)->exists()) {
                        // Create a new row for the user's current dA alias
                        DB::table('user_aliases')->insert([
                            [
                                'user_id'          => $user->id,
                                'site'             => 'deviantart',
                                'alias'            => $user->alias,
                                'is_visible'       => 1,
                                'is_primary_alias' => 1,
                            ],
                        ]);

                        // Clear the user's alias in the users table and set the has_alias bool in its place
                        $user->update([
                            'alias'     => null,
                            'has_alias' => 1,
                        ]);
                    }
                }
                $this->info('Migrated: User aliases');
            } else {
                $this->line('Skipped: User aliases (nothing to migrate)');
            }
        } else {
            $this->line('Skipped: User aliases (column no longer exists)');
        }

        $daAliases = UserAlias::where('site', 'dA')->get();
        if ($daAliases->count()) {
            $this->line('Updating '.$daAliases->count().' deviantArt aliases...');
            foreach ($daAliases as $alias) {
                $alias->site = 'deviantart';
                $alias->save();
            }
            $this->info('deviantArt aliases updated!');
        } else {
            $this->line('No deviantArt aliases to update!');
        }

        /* MOVE CHARACTER OWNER ALIASES */
        if (Schema::hasColumn('characters', 'owner_alias')) {
            // This and the following section operate on the assumption that all aliases to this point have been dA accounts

            // Get characters with an owner identified by alias
            $aliasCharacters = Character::whereNotNull('owner_alias')->get();

            if ($aliasCharacters->count()) {
                foreach ($aliasCharacters as $character) {
                    // Just in case, check to update character ownership
                    $userAlias = UserAlias::where('site', 'deviantart')->where('alias', $character->owner_alias)->first();
                    if ($userAlias) {
                        $character->update(['owner_alias' => null, 'user_id' => $userAlias->user_id]);
                    } elseif (!$userAlias) {
                        $alias = $character->owner_alias;
                        $character->update(['owner_alias' => null, 'owner_url' => 'https://deviantart.com/'.$alias]);
                    }
                }

                $this->info('Migrated: Character owner aliases');

                $nullCharacters = Character::where('owner_url', 'https://deviantart.com/null')->get();
                if ($nullCharacters->count()) {
                    $this->info('Fixing character owner URLs...');

                    foreach ($nullCharacters as $character) {
                        $logs = UserCharacterLog::where('character_id', $character->id)->where('recipient_url', '!=', null)->orderBy('id', 'DESC')->first();
                        if ($logs) {
                            $character->owner_url = $logs->recipient_url;
                            $character->save();
                        }
                    }
                    $this->info('Fixed: Character owner URLs');
                } else {
                    $this->line('Skipped: Character owner URL fixes (nothing to fix)');
                }
            } else {
                $this->line('Skipped: Character owner aliases (nothing to migrate)');
            }
        } else {
            $this->line('Skipped: Character owner aliases (column no longer exists)');
        }

        if (Schema::hasColumn('character_image_creators', 'alias')) {
            /** MOVE CHARACTER IMAGE CREATOR ALIASES */

            // Get character image creators with a set alias
            $aliasImageCreators = CharacterImageCreator::whereNotNull('alias')->get();

            if ($aliasImageCreators->count()) {
                foreach ($aliasImageCreators as $creator) {
                    $userAlias = UserAlias::where('site', 'deviantart')->where('alias', $creator->alias)->first();
                    if ($userAlias) {
                        $creator->update(['alias' => null, 'user_id' => $userAlias->user_id]);
                    } elseif (!$userAlias) {
                        $alias = $creator->alias;
                        $creator->update(['alias' => null, 'url' => 'https://deviantart.com/'.$alias]);
                    }
                }

                $this->info('Migrated: Character image creator aliases');
            } else {
                $this->line('Skipped: Character image creator aliases (nothing to migrate)');
            }
        } else {
            $this->line('Skipped: Character image creator aliases (column no longer exists)');
        }

        /* MOVE CHARACTER LOG ALIASES */

        if (Schema::hasColumn('character_log', 'recipient_alias') || Schema::hasColumn('character_log', 'sender_alias')) {
            // Get character logs with a set recipient alias
            $aliasCharacterLogs = CharacterLog::whereNotNull('recipient_alias')->get();
            $aliasCharacterLogsSender = CharacterLog::whereNotNull('sender_alias')->get();

            if ($aliasCharacterLogs->count()) {
                foreach ($aliasCharacterLogs as $characterLog) {
                    $userAlias = UserAlias::where('site', 'deviantart')->where('alias', $characterLog->recipient_alias)->first();
                    if ($userAlias) {
                        $characterLog->update(['recipient_alias' => null, 'recipient_id' => $userAlias->user_id]);
                    } elseif (!$userAlias) {
                        $alias = $characterLog->recipient_alias;
                        $characterLog->update(['recipient_alias' => null, 'recipient_url' => 'https://deviantart.com/'.$alias]);
                    }
                }

                foreach ($aliasCharacterLogsSender as $characterLog) {
                    $userAlias = UserAlias::where('site', 'deviantart')->where('alias', $characterLog->sender_alias)->first();
                    if ($userAlias) {
                        $characterLog->update(['sender_alias' => null, 'sender_id' => $userAlias->user_id]);
                    } elseif (!$userAlias) {
                        $alias = $characterLog->sender_alias;
                        $characterLog->update(['sender_alias' => null, 'sender_url' => 'https://deviantart.com/'.$alias]);
                    }
                }

                $this->info('Migrated: Character log aliases');
            } else {
                $this->line('Skipped: Character log aliases (nothing to migrate)');
            }
        } else {
            $this->line('Skipped: Character log aliases (column no longer exists)');
        }

        if (Schema::hasColumn('user_character_log', 'recipient_alias') || Schema::hasColumn('user_character_log', 'sender_alias')) {
            // Get character logs with a set recipient alias
            $aliasUserCharacterLogs = UserCharacterLog::whereNotNull('recipient_alias')->get();
            $aliasUserCharacterLogsSender = UserCharacterLog::whereNotNull('sender_alias')->get();

            if ($aliasUserCharacterLogs->count() || $aliasUserCharacterLogsSender->count()) {
                foreach ($aliasUserCharacterLogs as $characterLog) {
                    $userAlias = UserAlias::where('site', 'deviantart')->where('alias', $characterLog->recipient_alias)->first();
                    if ($userAlias) {
                        $characterLog->update(['recipient_alias' => null, 'recipient_id' => $userAlias->user_id]);
                    } elseif (!$userAlias) {
                        $alias = $characterLog->recipient_alias;
                        $characterLog->update(['recipient_alias' => null, 'recipient_url' => 'https://deviantart.com/'.$alias]);
                    }
                }

                foreach ($aliasUserCharacterLogsSender as $characterLog) {
                    $userAlias = UserAlias::where('site', 'deviantart')->where('alias', $characterLog->sender_alias)->first();
                    if ($userAlias) {
                        $characterLog->update(['sender_alias' => null, 'sender_id' => $userAlias->user_id]);
                    } elseif (!$userAlias) {
                        $alias = $characterLog->sender_alias;
                        $characterLog->update(['sender_alias' => null, 'sender_url' => 'https://deviantart.com/'.$alias]);
                    }
                }

                $this->info('Migrated: User character log aliases');
            } else {
                $this->line('Skipped: User character log aliases (nothing to migrate)');
            }
        } else {
            $this->line('Skipped: User character log aliases (column no longer exists)');
        }

        if (Schema::hasColumn('items', 'artist_alias')) {
            // Get character logs with a set recipient alias
            $aliasItemArtists = Item::whereNotNull('artist_alias')->get();

            if ($aliasItemArtists->count()) {
                foreach ($aliasItemArtists as $itemArtist) {
                    $userAlias = UserAlias::where('site', 'deviantart')->where('alias', $itemArtist->artist_alias)->first();
                    if ($userAlias) {
                        $itemArtist->update(['artist_alias' => null, 'artist_id' => $userAlias->user_id]);
                    } elseif (!$userAlias) {
                        $alias = $itemArtist->artist_alias;
                        $itemArtist->update(['artist_alias' => null, 'artist_url' => 'https://deviantart.com/'.$alias]);
                    }
                }

                $this->info('Migrated: Item artist aliases');
            } else {
                $this->line('Skipped: Item artist aliases (nothing to migrate)');
            }
        } else {
            $this->line('Skipped: Item artist aliases (column no longer exists)');
        }

        if ($this->option('drop-columns')) {
            // Drop alias columns from the impacted tables.
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('alias');
            });
            Schema::table('characters', function (Blueprint $table) {
                $table->dropColumn('owner_alias');
            });
            Schema::table('character_image_creators', function (Blueprint $table) {
                $table->dropColumn('alias');
            });
            Schema::table('character_log', function (Blueprint $table) {
                //
                $table->dropColumn('sender_alias');
                $table->dropColumn('recipient_alias');
            });
            Schema::table('user_character_log', function (Blueprint $table) {
                //
                $table->dropColumn('sender_alias');
                $table->dropColumn('recipient_alias');
            });
            Schema::table('items', function (Blueprint $table) {
                //
                $table->dropColumn('artist_alias');
            });
            $this->info('Dropped alias columns');
        } else {
            $this->line('Skipped: Dropping alias columns');
        }

        $this->line("\nAlias information migrated!");
        if (!$this->option('drop-columns')) {
            $this->line("After checking that all data has been moved from them,\nrun again with --drop-columns to drop alias columns if desired.");
        }

        return Command::SUCCESS;
    }
}

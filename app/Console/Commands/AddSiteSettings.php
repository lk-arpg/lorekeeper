<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;

class AddSiteSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add-site-settings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds the default site settings.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Add a site setting.
     *
     * Example usage:
     * $this->addSiteSetting("site_setting_key", 1, "0: does nothing. 1: does something.");
     *
     * @param  string  $key
     * @param  int     $value
     * @param  string  $description
     */
    private function addSiteSetting($key, $value, $description) {
        if(!DB::table('site_settings')->where('key', $key)->exists()) {
            DB::table('site_settings')->insert([
                [
                    'key'         => $key,
                    'value'       => $value,
                    'description' => $description,
                ],
            ]);
            $this->info( "Added:   ".$key." / Default: ".$value);
        }
        else $this->line("Skipped: ".$key);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('*********************');
        $this->info('* ADD SITE SETTINGS *');
        $this->info('*********************'."\n");

        $this->line("Adding site settings...existing entries will be skipped.\n");

        $this->addSiteSetting('is_registration_open', 1, '0: Registration closed, 1: Registration open. When registration is closed, invitation keys can still be used to register.');

        $this->addSiteSetting('transfer_cooldown', 0, 'Number of days to add to the cooldown timer when a character is transferred.');

        $this->addSiteSetting('open_transfers_queue', 0, '0: Character transfers do not need mod approval, 1: Transfers must be approved by a mod.');

        $this->addSiteSetting('is_prompts_open', 1, '0: New prompt submissions cannot be made (mods can work on the queue still), 1: Prompts are submittable.');

        $this->addSiteSetting('is_claims_open', 1, '0: New claims cannot be made (mods can work on the queue still), 1: Claims are submittable.');

        $this->addSiteSetting('is_reports_open', 1, '0: New reports cannot be made (mods can work on the queue still), 1: Reports are submittable.');

        $this->addSiteSetting('is_myos_open', 1, '0: MYO slots cannot be submitted for design approval, 1: MYO slots can be submitted for approval.');

        $this->addSiteSetting('is_design_updates_open', 1, '0: Characters cannot be submitted for design update approval, 1: Characters can be submitted for design update approval.');

        $this->addSiteSetting('blacklist_privacy', 0, 'Who can view the blacklist? 0: Admin only, 1: Staff only, 2: Members only, 3: Public.');

        $this->addSiteSetting('blacklist_link', 0, '0: No link to the blacklist is displayed anywhere, 1: Link to the blacklist is shown on the user list.');

        $this->addSiteSetting('blacklist_key', 0, 'Optional key to view the blacklist. Enter "0" to not require one.');

        $this->addSiteSetting('design_votes_needed', 3, 'Number of approval votes needed for a design update or MYO submission to be considered as having approval.');

        $this->addSiteSetting('admin_user', 1, 'ID of the site\'s admin user.');

        $this->addSiteSetting('gallery_submissions_open', 1, '0: Gallery submissions closed, 1: Gallery submissions open.');

        $this->addSiteSetting('gallery_submissions_require_approval', 1, '0: Gallery submissions do not require approval, 1: Gallery submissions require approval.');

        $this->addSiteSetting('gallery_submissions_reward_currency', 0, '0: Gallery submissions do not reward currency, 1: Gallery submissions reward currency.');

        $this->addSiteSetting('group_currency', 1, 'ID of the group currency to award from gallery submissions (if enabled).');

        $this->addSiteSetting('is_queue_open', 1, '0: New queue submissions cannot be made (mods can work on the queue still), 1: Queue is submittable.');

        $this->line("\nSite settings up to date!");

    }
}

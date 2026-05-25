<?php

namespace App\Providers;

use App\Listeners\SendEmailVerificationNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider {
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class                                     => [
            SendEmailVerificationNotification::class,
        ],

        \SocialiteProviders\Manager\SocialiteWasCalled::class => [
            'SocialiteProviders\\Deviantart\\DeviantartExtendSocialite@handle',
            'SocialiteProviders\\Instagram\\InstagramExtendSocialite@handle',
            'SocialiteProviders\\Tumblr\\TumblrExtendSocialite@handle',
            'SocialiteProviders\\Imgur\\ImgurExtendSocialite@handle',
            'SocialiteProviders\\Twitch\\TwitchExtendSocialite@handle',
            'SocialiteProviders\\Discord\\DiscordExtendSocialite@handle',
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot() {
        parent::boot();

        //
    }
}

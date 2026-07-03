<?php

namespace App\Providers;

use App\Providers\Socialite\ToyhouseProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     */
    public function register() {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot() {
        //
        Schema::defaultStringLength(191);
        Paginator::defaultView('layouts._pagination');
        Paginator::defaultSimpleView('layouts._simple-pagination');

        /*
         * Paginate a standard Laravel Collection.
         *
         * @param int $perPage
         * @param int $total
         * @param int $page
         * @param string $pageName
         * @return array
         */
        Collection::macro('paginate', function ($perPage, $total = null, $page = null, $pageName = 'page') {
            $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);

            return new LengthAwarePaginator(
                $this->forPage($page, $perPage),
                $total ?: $this->count(),
                $perPage,
                $page,
                [
                    'path'     => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => $pageName,
                ]
            );
        });

        $this->bootToyhouseSocialite();

        // Set custom polymorphic types for rewards and limits dynamically based on asset keys
        // Merge both kinds of asset arrays, all of the limit types, and all of the loot types into one array
        $models = array_unique(array_merge(getAssetKeys(), getAssetKeys(true), array_keys(config('lorekeeper.limits.limit_types')), array_map('strtolower', array_keys(config('lorekeeper.loot_types')))));
        // Create the initial morph map by feeding the above into getAssetModelString()
        $morphMap = array_combine(array_values($models), array_map('getAssetModelString', array_values($models)));
        // Take all the model strings above and pair them with their class base name
        $modelStrings = array_map('getAssetModelString', array_map('strtolower', array_values($models)));
        // Finally, combine it all into a final morph map of alias => model string
        $morphMap = array_merge($morphMap, array_combine(array_map(fn ($model) => class_basename($model), $modelStrings), $modelStrings));

        Relation::morphMap($morphMap);
    }

    /**
     * Boot Toyhouse Socialite provider.
     */
    private function bootToyhouseSocialite() {
        $socialite = $this->app->make('Laravel\Socialite\Contracts\Factory');
        $socialite->extend(
            'toyhouse',
            function ($app) use ($socialite) {
                $config = $app['config']['services.toyhouse'];

                return $socialite->buildProvider(ToyhouseProvider::class, $config);
            }
        );
    }
}

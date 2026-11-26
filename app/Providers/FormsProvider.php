<?php

namespace App\Providers;

use App\Helpers\FormAdapter;
use Illuminate\Support\ServiceProvider;

class FormsProvider extends ServiceProvider {
    /**
     * Register bindings in the container.
     */
    public function register() {
        $this->registerFormAdapter();

        $this->app->alias('form', FormAdapter::class);
    }

    /**
     * Register the HTML adapter instance.
     */
    protected function registerFormAdapter() {
        $this->app->singleton('form', function () {
            return new FormAdapter;
        });
    }
}

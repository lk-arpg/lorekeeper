<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Forms extends Facade {
    protected static function getFacadeAccessor() {
        return 'form';
    }
}

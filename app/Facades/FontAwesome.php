<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class FontAwesome extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'fontawesome';
    }
}

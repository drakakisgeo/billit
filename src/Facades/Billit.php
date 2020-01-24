<?php

namespace Drakakisgeo\Billit\Facades;

use Illuminate\Support\Facades\Facade;

class Billit extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'billit';
    }

}
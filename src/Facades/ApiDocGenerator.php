<?php

namespace Alagiesinghateh\LaravelApiDocGenerator\Facades;

use Illuminate\Support\Facades\Facade;

class ApiDocGenerator extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'api-doc-generator';
    }
}

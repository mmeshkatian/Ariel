<?php

namespace Mmeshkatian\Ariel;

use Illuminate\Support\Facades\Facade as Facader;

class Facade extends Facader
{
    /**
     * The name of the binding in the IoC container.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ariel';
    }
}

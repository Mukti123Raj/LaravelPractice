<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Assignment extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'assignment.service';
    }
}



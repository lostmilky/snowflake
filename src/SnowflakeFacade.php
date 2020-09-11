<?php

namespace Lostmilky\Snowflake;

use \Illuminate\Support\Facades\Facade;

class SnowflakeFacade extends Facade {

    protected static function getFacadeAccessor() {
        return 'Snowflake';
    }
}
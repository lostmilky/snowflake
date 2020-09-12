<?php
namespace Lostmilky\Snowflake\Facades;

use \Illuminate\Support\Facades\Facade;

class Snowflake extends Facade {

    protected static function getFacadeAccessor() {
        return 'Snowflake';
    }
}
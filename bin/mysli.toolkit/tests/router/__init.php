<?php

namespace mysli\toolkit\root\tests\router;

use mysli\toolkit\router as original_router;

class router extends original_router {

    protected static $routes = [];

    static function reset()
    {
        static::$routes = [];
    }

    protected static function read()
    {
        return;
    }

    protected static function write()
    {
        return true;
    }
}

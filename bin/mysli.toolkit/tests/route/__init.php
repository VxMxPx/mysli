<?php

namespace mysli\toolkit\root\tests\route;
use mysli\toolkit\route as original_route;
\autoloader::resolve_use(__NAMESPACE__.'\route', original_route::__use);

class route extends original_route
{
    // Null __init

    static function __init($path=null)
    {
        return true;
    }

    // Testing related methods

    static function t__reset()
    {
        static::$r = [];
    }

    static function t__add_data($data)
    {
        static::$r = clist::decode($data, static::$r_options);
        return is_array(static::$r);
    }

    // Null read/write

    static function reload()
    {
        return true;
    }

    static function write()
    {
        return true;
    }
}

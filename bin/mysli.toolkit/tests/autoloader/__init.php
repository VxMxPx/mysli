<?php

namespace mysli\toolkit\root\tests\autoloader;

class autoloader extends \mysli\toolkit\autoloader
{
    protected static $aliases = [];
    protected static $initialized = ['mysli.toolkit'];
    protected static $overrides = [];

    static function __t_dump()
    {
        return static::$aliases;
    }

    static function __t_reset()
    {
        static::$aliases = static::$initialized = static::$overrides = [];
    }

    protected static function make_alias($class, $alias)
    {
        return true;
    }

    protected static function init_class($class)
    {
        return true;
    }
}



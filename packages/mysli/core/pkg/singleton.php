<?php

namespace Mysli\Core\Pkg;

trait Singleton
{
    private static $pkg_auto_construct_type = 'singleton';
    // // private static $pkg_instance = null;

    // // public static function pkg_instance($instance = null)
    // // {
    // //     if ($instance && !self::$pkg_instance) {
    // //         self::$pkg_instance = $instance;
    // //     }
    // //     return self::$pkg_instance;
    // // }
}

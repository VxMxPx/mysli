<?php

namespace Mysli\Pkgm;

class Autoloader
{
    /**
     * Autloader method.
     * --
     * @param  string $class Full class name (namespace included), e.g.: \Mysli\Core\Core
     * --
     * @throws PackageException If trying to autoload class of disabled (or non existent) package.
     * @throws NotFoundException If required file for class is not found.
     * --
     * @return boolean
     */
    public static function load($class)
    {
        // Cannot handle non-namespaced requests!
        if (strpos($class, '\\') === false) { return false; }

        // Autoload will always get the class, convert it to package!
        $package = Util::to_pkg($class, Util::BASE);

        // Are we dealing with exception?
        if (substr($class, -9, 9) === 'Exception') {
            $segments = explode('/', Util::to_pkg($class));
            $filename = array_pop($segments);
            $filename = substr($filename, 0, -10); // _exception
            $filename = pkgpath($package . '/exceptions/' . $filename . '.php');
        } else {
            $filename = pkgpath(Util::to_path($class));
        }

        if (!file_exists($filename)) {
            return false;
        }

        include $filename;

        return class_exists($class, false);
    }
}

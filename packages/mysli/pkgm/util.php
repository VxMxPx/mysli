<?php

namespace Mysli\Pkgm;

class Util
{
    const BASE = 2;
    const FILE = 4;
    const FULL = 6; // Shortcut: BASE | FILE

    /**
     * Convert class or package to filename.
     * E.g.: vendor/package => vendor/package/package.php
     *       vendor/package/sub => vendor/package/sub.php
     *       Vendor\Package\Package => vendor/package/package.php
     * --
     * @param  string $input
     * --
     * @return string
     */
    public static function to_path($input)
    {
        // Do we have namespaced class? Vendor\Package\...
        if (strpos($input, '\\') !== false) {
            $input = trim($input, '\\');
            $input = \Core\Str::to_underscore($input);
            $input = strtolower($input);
            $input = str_replace('\\', '/', $input);
        }

        // Do we have only base package name vendor/package
        if (substr_count($input, '/') < 2) {
            $input = explode('/', $input);
            $input[] = $input[1];
            $input = implode('/', $input);
        }

        return $input . '.php';
    }

    /**
     * (Namespaced Class to) Package.
     * E.g.: Vendor\Package\Sub\Class => vendor/package (Util::BASE)
     *       Vendor\Package\Sub\Class => sub/class (Util::FILE)
     *       Vendor\Package\Sub\Class => vendor/package/sub/class (Util::FULL)
     * --
     * @param  string  $nsc
     * @param  integer $mode (Util::BASE, Util::FILE, Util::FULL)
     * --
     * @throws \Core\ValueException If class name has less than two segments. (1)
     * @throws \Core\ValueException If mode is not valid. (2)
     * --
     * @return string
     */
    public static function to_pkg($nsc, $mode = self::FULL)
    {
        if (strpos($nsc, '/') !== false) {
            $pkg = explode('/', $nsc);
        } else {
            $pkg = trim($nsc, '\\');
            $pkg = \Core\Str::to_underscore($pkg);
            $pkg = strtolower($pkg);
            $pkg = explode('\\', $pkg);
        }

        // Less than two, means error!
        if (count($pkg) < 2) {
            throw new \Core\ValueException(
                "Invalid class - need at least two segments: `{$nsc}`.", 1
            );
        }

        // Do we need to add file name? e.g.: vendor/package => vendor/package/package
        if (count($pkg) === 2) {
            $pkg[] = $pkg[1];
        }

        switch ($mode) {
            case self::BASE:
                return implode('/', array_slice($pkg, 0, 2));

            case self::FILE:
                return implode('/', array_slice($pkg, 2));

            case self::FULL:
                return implode('/', $pkg);

            default:
                throw new \Core\ValueException("Invalid mode: `{$mode}`.", 2);
        }
    }

    /**
     * (Package name to) (Namespaced) Class.
     * E.g.: vendor/package     => \Vendor\Package (Util::BASE)
     *       vendor/package/sub => Sub =>  (Util::FILE)
     *       vendor/package/sub => \Vendor\Package\Sub (Util::FULL)
     *       vendor/package     => \Vendor\Package\Package (Util::FULL)
     * --
     * @param  string  $pkg
     * @param  integer $mode (Util::BASE, Util::FILE, Util::FULL)
     * --
     * @throws \Core\ValueException If package name has less than two segments. (1)
     * @throws \Core\ValueException If mode is not valid. (2)
     * --
     * @return string
     */
    public static function to_class($pkg, $mode = self::FULL)
    {
        if (strpos($pkg, '\\') !== false) {
            $class = trim($pkg, '\\');
            $class = explode('\\', $class);
        } else {
            $class = \Core\Str::to_camelcase($pkg);
            $class = explode('/', $class);
        }

        // Less than two, means error!
        if (count($class) < 2) {
            throw new \Core\ValueException(
                "Invalid package name - need at least two segments: `{$pkg}`.", 1
            );
        }

        // Do we need to add class name? e.g.: vendor/package => Vendor\Package\Package
        if (count($class) === 2) {
            $class[] = $class[1];
        }

        switch ($mode) {
            case self::BASE:
                return '\\' . implode('\\', array_slice($class, 0, 2));

            case self::FILE:
                return implode('\\', array_slice($class, 2));

            case self::FULL:
                return '\\' . implode('\\', $class);

            default:
                throw new \Core\ValueException("Invalid mode: `{$mode}`.", 2);
        }
    }
}

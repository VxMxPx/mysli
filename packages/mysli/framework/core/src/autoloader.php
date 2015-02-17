<?php

namespace mysli\framework\core;

class autoloader {

    private static $packages    = [];
    private static $aliases     = [];
    private static $initialized = ['mysli.framework.core'];

    /**
     * Init the autoloader set packages list.
     */
    static function __init(array $packages) {
        self::$packages = $packages;
    }

    /**
     * Add/remove package from packages list for purposes of package being able
     * to be autoloaded without being enabled.
     * This is mostly used in a installation phase of the system, above all
     * pkgm will use it to enable itself.
     * @param  string $package
     * @param  string $path if not provided, package will be removed from list
     */
    static function __modify_packages_list($package, $path=null) {
        if (!$path) {
            if (isset(self::$packages[$package])) {
                unset(self::$packages[$package]);
            }
        } else {
            self::$packages[$package] = $path;
        }
    }

    /**
     * Autoloader
     * @param  string $class
     * @return boolean
     */
    static function load($class) {

        // See if it's set in aliases
        if (isset(self::$aliases[$class])) {
            if (self::load_class(self::$aliases[$class])) {
                class_alias(self::$aliases[$class], $class);
                return true;
            } else {
                return false;
            }
        }

        if (self::load_class($class)) {
            return true;
        }

        // See if it's set as regex in aliseas
        foreach (self::$aliases as $pattern => $required) {

            // Only regex classes here
            if (substr($pattern, 0, 1) !== '/') {
                continue;
            }

            if (preg_match($pattern, $class, $match)) {
                $required = str_replace('*', $match[1], $required);
                if (self::load_class($required)) {
                    class_alias($required, $class);
                    return true;
                } else {
                    return false;
                }
            }
        }

        return false;
    }
    /**
     * Add alias to be considered when autoloading.
     * @param string $from vendor\package\{...}
     * @param string $as   vendor\package\{...}
     */
    static function add_alias($from, $as) {

        if ($from === $as) {
            return;
        }

        // Resolve *
        if (strpos($as, '*') !== false) {
            // $as = str_replace('/', '\\', $as);
            $as = preg_quote($as);
            $as = str_replace('\\*', '(.*?)', $as);
            $as = "/^{$as}$/";
        }

        // Correct / in alias
        // $from = str_replace('.', '\\', $from);
        // if (!strpos($from, '*') && !strpos($from, ',')) {
        //     list($_, $_, $_, $from, $alias) = self::resolve_class($from);
        //     dump_r("From, alias", $from, $alias);
        //     if (!$from) {
        //         throw new \Exception(
        //             "Couldn't resolve class:\n>> {$from} << AS {$as}");
        //     }
        // }

        if (isset(self::$aliases[$as]) && self::$aliases[$as] !== $from) {
            throw new \Exception(
                "Failed:\n   ".self::$aliases[$as] . " AS {$as}\n".
                ">> {$from} AS {$as}\n");
        }

        // As is being unique here, so we set it as key
        // there can be multiple `from` for different as
        if ($as !== $from) {
            self::$aliases[$as] = $from;
        }
    }

    /**
     * Resolve use statement.
     * @param  string $namespace
     * @param  string $use
     * @return boolean
     */
    static function resolve_use($namespace, $use) {

        $lines = explode("\n", $use);
        $segments = explode('\\', $namespace);

        if (isset(self::$packages[implode('.', array_slice($segments, 0, 3))]))
        {
            $rootc = 3;
        }
        elseif (isset(self::$packages[implode('.', array_slice($segments, 0, 2))]))
        {
            $rootc = 2;
        }
        else
        {
            throw new \Exception(
                "Not found: `{$namespace}`, perhaps package is not enabled."
            );
        }

        $package = implode('.', array_slice($segments, 0, $rootc));

        foreach ($lines as $line) {

            $line = trim(strtolower($line));

            // Empty line, skip
            if (empty($line)) {
                continue;
            }

            // Comment, skip
            if (substr($line, 0, 1) === '#') {
                continue;
            }

            // is it internal?
            if (substr($line, 0, 2) === './') {
                $line = $package.'/'.substr($line, 2);
            }

            // Contains AS?
            if (strpos($line, ' as ')) {
                list($from, $as) = explode(' as ', $line, 2);
                $as   = trim($as);
                $from = trim($from);
            } else {
                $from = $line;
                // namespace.package/class ...
                if (strpos($line, '/')) {
                    $as = explode('/', $line)[1];
                } else {
                    // ... or namespace.package
                    $as   = substr($line, strrpos($line, '.')+1);
                    // $from = $from.'/'.substr($from, strrpos($from, '.')+1);
                    $from = $from.'/'.$as;
                }
            }

            // is multiple insersion?
            if (strpos($from, ',')) {
                $sfrom = explode('/', $from, 2);
                $last_from = trim($sfrom[1]);
                $from = $sfrom[0];
                // $from = implode('/', array_slice($sfrom, 0, -1));
                $multiple_from = explode(',', $last_from);

                if (strpos($as, ',')) {
                    $segments_as = explode('\\', $as);
                    $as = implode('\\', array_slice($segments_as, 0, -1));
                    $last_as = trim(array_slice($segments_as, -1)[0]);
                    $multiple_as = explode(',', $last_as);
                    if (count($multiple_as) !== count($multiple_from)) {
                        throw new \Exception(
                            "Expected the same amout of elements: ".
                            "`{$line}` when using `AS`. (".
                            implode(',', $multiple_from).") != (".
                            implode(',', $multiple_as).")");
                    }
                } else {
                    $multiple_as = $multiple_from;
                }

                foreach ($multiple_from as $k => $file) {
                    if (strpos($as, '*')) {
                        $asf = str_replace('*', $multiple_as[$k], $as);
                    } else {
                        $asf = trim($as.'\\'.$multiple_as[$k], '\\');
                    }
                    $asf = $namespace.'\\'.$asf;
                    \core\autoloader::add_alias(
                        str_replace(['.', '/'], '\\', "{$from}\\{$file}"),
                        $asf
                    );
                }
                continue;
            }

            $as = $namespace.'\\'.$as;
            \core\autoloader::add_alias(str_replace(['.', '/'], '\\', $from), $as);
        }
    }

    // Private

    /**
     * Resolve class to filename, and add actual class to namespace
     * if missing, e.g.: vendor/package => vendor/package/packag (class)
     * @param  string $class
     * @return array  [
     *         $root,     // Root package name
     *         $abs_path, // Class full absolute path
     *         $rel_path, // Class relative path (inc. filename, path)
     *         $class,    // Full resolved class name
     *         $alias     // Short class name (to be aliased as)
     * ]
     */
    private static function resolve_class($class) {

        $segments = explode("\\", $class);

        if (isset(self::$packages[implode('.', array_slice($segments, 0, 3))]))
        {
            $rootc = 3;
        }
        elseif (isset(self::$packages[implode('.', array_slice($segments, 0, 2))]))
        {
            $rootc = 2;
        }
        else
        {
            throw new \Exception(
                "Call not found: `{$class}`, perhaps package is not enabled."
            );
        }

        // get root (vendor.sub.package)
        $root = implode('.', array_slice($segments, 0, $rootc));

        // get sub-path
        if (count($segments) > $rootc+1) {
            $spath = implode('/', array_slice($segments, $rootc, -1)) . '/';
        } else {
            $spath = '';
        }

        // get file
        if (count($segments) === $rootc) {
            $alias = implode('\\', $segments);
        } else {
            $alias = false;
        }

        $file = array_slice($segments, -1)[0];
        $path = self::$packages[$root];
        $phar = strpos($path, '.');

        $rel_path = "{$spath}{$file}.php";
        $abs_path = MYSLI_PKGPATH."/{$path}/src";
        $path  = $phar ? 'phar://'.$abs_path : $abs_path;

        return [$root, $abs_path, $rel_path, $class, $alias];
    }
    /**
     * Load actual class (and initialized it if required)
     * @param  string $class
     * @return boolean
     */
    private static function load_class($class) {

        list($root, $abs_path, $rel_path, $class, $alias) = self::resolve_class($class);
        $path = "{$abs_path}/{$rel_path}";


        if (!file_exists($path)) {
            return false;
        }

        if (!class_exists($class, false) && !trait_exists($class, false)) {
            include($path);
        }

        if (!class_exists($class, false) && !trait_exists($class, false)) {
            throw new \Exception(
                "Class: `{$class}` not found in: `{$path}`.", 1);
        }

        if ($alias && !class_exists($alias, false)) {
            class_alias($class, $alias);
        }

        if (!in_array($root, self::$initialized)) {
            self::initialize($root, $abs_path);
        }

        return true;
    }

    /**
     * Run init if this package is loader first time.
     * @param  string  $package
     * @return null
     */
    private static function initialize($package, $path) {
        // under no circumstance load __init twice
        self::$initialized[] = $package;

        $function = '\\'.str_replace('.', '\\', $package.'\\__init');
        $path = $path."/__init.php";

        if (!function_exists($function)) {
            if (file_exists($path)) {
                include($path);
            }
        }

        if (function_exists($function)) {
            call_user_func($function);
        }
    }
}

<?php

namespace mysli\framework\core;

class autoloader {

    private static $aliases     = [];
    private static $initialized = ['mysli.framework.core'];

    /**
     * Load fully namespaced class, if package is enabled.
     * Also resolve aliases
     * @param  string $class
     * @return boolean
     */
    static function load($class)
    {
        if (isset(self::$aliases[$class]))
        {
            if (self::init_class(self::$aliases[$class]))
            {
                class_alias(self::$aliases[$class], $class);
                return true;
            }
            else
            {
                return false;
            }
        }

        if (self::init_class($class))
            return true;

        foreach (self::$aliases as $pattern => $required)
        {
            if (substr($pattern, 0, 1) !== '/')
                continue;

            if (preg_match($pattern, $class, $match))
            {
                $required = str_replace('*', $match[1], $required);
                if (self::init_class($required))
                {
                    class_alias($required, $class);
                    return true;
                }
                else
                {
                    return false;
                }
            }
        }

        return false;
    }
    /**
     * Resolve __use statement.
     * @param  string $namespace
     * @param  string $use
     */
    static function ruse($namespace, $use)
    {
        $segments = explode('\\', $namespace);
        $self_package = \core\pkg::has(implode('.', array_slice($segments, 0, 2))) ?
            implode('.', array_slice($segments, 0, 2)) :
            implode('.', array_slice($segments, 0, 3));

        foreach (explode("\n", $use) as $lineno => $line)
        {
            $line = trim(strtolower($line));

            if (empty($line))
                continue;

            // Comment
            if (substr($line, 0, 1) === '#')
                continue;

            // ./pkg => self.vendor.package/pkg
            if (substr($line, 0, 2) === './')
                $line = $self_package.'/'.substr($line, 2);

            // Contain ' as '?
            if (strpos($line, ' as '))
            {
                list($from, $as) = explode(' as ', $line, 2);
                $from = trim($from);
                $as   = trim($as);
            }
            else
            {
                $from = $line;

                // Is it in format: vendor.sub.package/class
                // Set `as` to be `class`
                if (strpos($line, '/'))
                {
                    $as = array_pop(explode('/', $line, 2));
                }
                else
                {
                    $as   = substr($line, strrpos($line, '.')+1);
                    $from = "{$from}/{$as}";
                }

                $as = str_replace('/', '\\', $as);
            }

            // Multiple classes in one line, e.g.:
            // mysli.framework.core/one,two,three
            if (strpos($from, ','))
            {
                if (!strpos($as, ','))
                    throw new \Exception(
                        "Wrong assignment: `{$line}`, ".
                        "`as` need to be list of aliases.", 10
                    );

                $lc_from = substr($from, strrpos($from, '/')+1);
                $from = substr($from, 0, strlen($from)-strlen($lc_from)-1);
                $lc_from = explode(',', $lc_from);

                if (!strpos($as, '*')) {
                    if (strpos($as, '\\') === false)
                        $as = "\\{$as}";

                    $lc_as = substr($as, strrpos($as, '\\')+1);
                    $as = substr($as, 0, strlen($as)-strlen($lc_as)-1);
                    $lc_as = explode(',', $lc_as);

                    if (count($lc_as) !== count($lc_from))
                        throw new \Exception(
                            "Number of elements in doesn't match: `{$line}`", 20
                        );
                }

                foreach ($lc_from as $lc_pos => $fclass)
                {
                    if (strpos($as, '*'))
                        $asf = str_replace('*', $fclass, $as);
                    else
                        if ($as)
                            $asf = "{$as}\\{$lc_as[$lc_pos]}";
                        else
                            $asf = $lc_as[$lc_pos];

                    $asf = "{$namespace}\\{$asf}";
                    $fromf = str_replace(['.', '/'], '\\', $from).'\\'.$fclass;
                    self::alias($fromf, $asf);
                }
                continue;
            }

            $as = "{$namespace}\\{$as}";
            $from = str_replace(['.', '/'], '\\', $from);
            self::alias($from, $as);
        }
    }

    // Private

    /**
     * Alias particular class (to <= as)
     * Allowed mysli\framework\core\* as core\*
     * This will set for example
     * mysli\framework\pkgm\pkgm <= mysli\framework\cli\pkgm
     * @param  string $to
     * @param  string $as
     */
    private static function alias($to, $as)
    {
        if ($to === $as)
            return;

        if (strpos($as, '*')) {
            $as = preg_quote($as);
            $as = str_replace('\\*', '(.*?)', $as);
            $as = "/^{$as}$/";
        }

        if (isset(self::$aliases[$as]) && self::$aliases[$as] !== $to)
            throw new \Exception(
                "Alias is already set `{$as}`, for `{self::$aliases[$as]}`, ".
                "cannot rewrite it for `{$to}`", 10
            );

        self::$aliases[$as] = $to;
    }
    /**
     * Load particular class file, if file exists.
     * Call __init, if available and was not called before.
     * @param  string $class
     * @return boolean
     */
    private static function init_class($class)
    {
        // Our work here is done
        if (class_exists($class, false))
            return true;

        // Get pckage's name...
        $segments = explode('\\', $class);
        $rootc = \core\pkg::has(implode('.', array_slice($segments, 0, 2))) ? 2 : 3;
        $package = implode('.', array_slice($segments, 0, $rootc));
        $release = \core\pkg::get_release_by_name($package);

        if (!$release)
            return false;

        // Get paths
        $relpath = implode('/', array_slice($segments, $rootc)).'.php';
        $is_phar = !strpos($release, '/');
        $abspath = MYSLI_PKGPATH.'/'.$release;
        if ($is_phar)
            $abspath = "phar://{$abspath}.phar/src";
        else
            $abspath = "{$abspath}/src";

        $class_file = "{$abspath}/{$relpath}";

        if (!file_exists($class_file))
            return false;
            // throw new \Exception("Class file not found: `{$class_file}`", 10);
        else
            include($class_file);

        if (!class_exists($class, false) && !trait_exists($class, false))
            throw new \Exception(
                "File was loaded: `{$class_file}`, ".
                "but class: `{$class}` was not found.", 20
            );

        if (!in_array($package, self::$initialized))
            self::init($package, $abspath);

        return true;
    }
    /**
     * Run __init for particular package
     * @param  string $package
     * @param  string $path
     */
    private static function init($package, $path)
    {
        self::$initialized[] = $package;

        $function = '\\'.str_replace('.', '\\', $package).'\\__init';
        $path = $path.'/__init.php';

        if (!file_exists($path))
            return;

        if (!function_exists($function))
            include($path);

        if (function_exists($function))
            call_user_func($function);
        else
            throw new \Exception(
                "Init function `{$function}` not found in: `{$path}`.", 10);
    }
}

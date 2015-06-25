<?php

/**
 * # Autoloader
 *
 * Handle autoloading of classes, aliasing (when __use exists)
 * and initialization of packages.
 */
namespace mysli\toolkit; class autoloader
{
    /**
     * List of aliased classed.
     * --
     * @var array
     */
    private static $aliases = [];

    /**
     * List of initialized packages.
     * --
     * @var array
     */
    private static $initialized = ['mysli.toolkit'];

    /**
     * Load class, init package, load dependencies.
     * Entry point.
     * --
     * @param string $class
     * --
     * @return boolean
     */
    static function load($class)
    {
        log::debug("Load: {$class}", __CLASS__);

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
        {
            return true;
        }

        foreach (self::$aliases as $pattern => $required)
        {
            if (substr($pattern, 0, 1) !== '/')
            {
                continue;
            }

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
     * --
     * @example
     * const __use = '
     *     vendor.package.{class, class_two, class_three -> three}
     * ';
     * --
     * @param string $class Including full namespace.
     * @param string $use
     */
    static function resolve_use($class, $use)
    {
        /*
        Collection of resolved aliases.
         */
        $aliases = [];

        /**
         * Get namespace from class.
         */
        $namespace = substr($class, 0, strrpos($class, '\\'));

        /*
        Get package's name from namespace, used for example: .{foo, bar}
         */
        $rt_pkg = pkg::by_namespace($namespace);

        /*
        Will be set to true in case of block, for example:
        vendor.package.{
            class,
            class2
        }
        $now_line will contain previous segments, until line will be finally
        constructed, e.g.: vendor.package.{class, class2}
         */
        $in_block = false;
        $now_line = '';

        /*
        Lines from use statement.
         */
        $lines = explode("\n", $use);

        /*
        Main loop
         */
        foreach ($lines as $lineno => $line)
        {
            $line = trim(strtolower($line));

            // Empty line, go on...
            if (empty($line))
            {
                continue;
            }

            // Comment, skip...
            if (substr($line, 0, 1) === '#')
            {
                continue;
            }

            // Opened { tag, which is not closed, this will opened a new block
            if (strpos($line, '{') && !strpos($line, '}'))
            {
                // Cannot open another block if block if already opened
                if ($in_block)
                {
                    throw new \Exception(f_error($lines, $lineno,
                        "Block already opened. This looks like a syntax error ".
                        "in __use statement for: `{$class}`."
                    ));
                }

                $in_block = true;
                $now_line = $line;

                continue;
            }

            // Closed block?
            if (!strpos($line, '{') && strpos($line, '}') !== false)
            {
                // Obviously cannot close if not opened...
                if (!$in_block)
                {
                    throw new \Exception(f_error($lines, $lineno,
                        "Block not opened. This looks like a syntax error ".
                        "in __use statement for: `{$class}`."
                    ));
                }

                // It's expected that last line has no semicolon.
                if (substr($now_line, -1) === ',')
                {
                    throw new \Exception(f_error($lines, $lineno-1,
                        "Unexpected semicolon (,) at the end of the line, when".
                        "closing block. For: `{$class}`."
                    ));
                }

                $line = $now_line.$line;
                $in_block = false;

                // Go on, this line need to be processed as it would normally be
                // ...
            }

            // Are we in block right now?
            if ($in_block)
            {
                // It's expected that line will end with `,` when we're in block
                if (substr($now_line, -1) !== ',' &&
                    substr($now_line, -1) !== '{')
                {
                    throw new \Exception(f_error($lines, $lineno-1,
                        "Expected semicolon (,) at the end of the line, when".
                        "in block. For: `{$class}`."
                    ));
                }

                $now_line .= $line;
                continue;
            }


            // Packages names can be written as vendor\package or vendor.package
            // Lets standardize this now.
            $line = str_replace('\\', '.', $line);

            // ./pkg => self.vendor.package/pkg
            if (substr($line, 0, 1) === '.')
            {
                $line = $rt_pkg.$line;
            }

            $aliases = array_merge(
                $aliases,
                self::resolve_use_line($line, $namespace)
            );
        }

        // Register all aliases
        foreach ($aliases as $from => $to)
        {
            self::register_alias($from, $to);
        }
    }

    /*
    --- Private ----------------------------------------------------------------
     */

    /**
     * Resolve line from __use statement.
     * --
     * @example
     * A complex line, in vendor\package namespace:
     *   vendor.package.{foo -> bar, baz -> bam}
     * Will return:
     * [
     *     ['vendor\package\bar' => 'vendor\package\foo'],
     *     ['vendor\package\bam' => 'vendor\package\baz']
     * ]
     * --
     * @param  string $line
     * @param  string $namespace
     * --
     * @return array  [ from => as, from => as, from => as ]
     */
    private static function resolve_use_line($line, $namespace)
    {
        /*
        Do we have { } ?
         */
        if (strpos($line, '{'))
        {
            // Initiate empty list to be returned
            $list = [];

            // Remove end }
            $line = trim($line, '}');

            // Explode by {, getting base and classes
            list($base, $classes) = explode('{', $line, 2);

            // Classes to array
            $classes = explode(',', $classes);

            // Resolve individual classes by calling this method again...
            foreach ($classes as $class)
            {
                $list = array_merge(
                    $list,
                    self::resolve_use_line($base.trim($class), $namespace)
                );
            }

            return $list;
        }

        /*
        Do we have named class (->), example vendor.package.class -> foo.bar
         */
        if (strpos($line, '->'))
        {
            list($from, $to) = explode('->', $line, 2);
            $from = str_replace('.', '\\', trim($from));
            $to   = str_replace('.', '\\', trim($to));
        }
        else
        {
            $from = str_replace('.', '\\', $line);
            $to   = substr($line, strrpos($line, '.')+1);
        }


        return [ $from =>  "{$namespace}\\{$to}"];
    }

    /**
     * Alias particular class (from => to)
     * Allowed mysli\framework\core\* -> core\*
     * --
     * @example
     * Resolve:
     * mysli\framework\pkgm\pkgm => mysli\framework\cli\pkgm
     * --
     * @param  string $from
     * @param  string $to
     */
    private static function register_alias($from, $to)
    {
        if ($from === $to)
            return;

        if (strpos($to, '*'))
        {
            $to = preg_quote($to, '/');
            $to = str_replace('\\*', '(.*?)', $to);
            $to = "/^{$to}$/";
        }

        if (isset(self::$aliases[$to]) && self::$aliases[$to] !== $from)
        {
            throw new \Exception(
                "Alias is already set `{$to}`, for `".self::$aliases[$to]."`, ".
                "cannot rewrite it for `{$from}`", 10
            );
        }

        log::debug("Register: `{$from}` => `{$to}`.", __CLASS__);
        self::$aliases[$to] = $from;
    }

    /**
     * Load particular class file, if file exists.
     * Register __use, if set.
     * Call __init, if available and was not called before.
     * --
     * @param  string $class
     * --
     * @return boolean
     */
    private static function init_class($class)
    {
        /*
        This class already exists, nothing to do here...
         */
        if (class_exists($class, false))
            return true;

        /*
        Get package's name from namespace
         */
        $package = pkg::by_namespace($class);
        $is_phar = pkg::exists_as($package) === pkg::phar;

        /*
        Resolve short namespaces (e.g. mysli\web\web => mysli\web\web\web)
         */
        if (substr_count($package, '.') == substr_count($class, '\\'))
        {
            $alias  = $class;
            $class .= substr($class, strrpos($class, '\\'));
        }
        else
        {
            $alias = false;
        }

        /*
        Get package's path
         */
        if ($is_phar)
        {
            $abspath = 'phar://'.MYSLI_BINPATH."/{$package}.phar/src";
        }
        else
        {
            $abspath = MYSLI_BINPATH."/{$package}/src";
        }

        /*
        Get class path
         */
        $segments = explode('\\', $class);
        $rootc    = substr_count($package, '.')+1;
        $relpath  = implode('/', array_slice($segments, $rootc)).'.php';

        $class_file = "{$abspath}/{$relpath}";

        if (!file_exists($class_file))
        {
            return false;
        }
        else
        {
            include $class_file;
        }

        /*
        File was loaded, but no class in it
         */
        if (!class_exists($class, false) && !trait_exists($class, false))
        {
            throw new \Exception(
                "File was loaded: `{$class_file}`, ".
                "but class: `{$class}` was not found.", 20
            );
        }

        /*
        Resolve use statement if there
         */
        if (defined("{$class}::__use"))
        {
            self::resolve_use(
                $class,
                constant("{$class}::__use")
            );
        }

        /*
        Init this package, if not initialized yet by some other class.
         */
        self::init($package, $abspath);

        /*
        Register short version of name this class
         */
        if ($alias)
        {
            self::register_alias($class, $alias);
            class_alias($class, $alias);
        }

        /*
        Well done!
         */
        return true;
    }

    /**
     * Run __init for particular package
     * --
     * @param  string $package
     * @param  string $path
     */
    private static function init($package, $path)
    {
        if (in_array($package, self::$initialized))
            return;

        log::debug("Initialize: `{$package}`.", __CLASS__);

        self::$initialized[] = $package;

        $class = '\\'.str_replace('.', '\\', $package).'\\__init';
        $path = $path.'/__init.php';

        if (!file_exists($path))
        {
            return;
        }

        if (!class_exists($class, false))
        {
            include($path);
        }

        if (class_exists($class, false) && method_exists($class, '__init'))
        {
            call_user_func([$class, '__init']);
        }
    }
}

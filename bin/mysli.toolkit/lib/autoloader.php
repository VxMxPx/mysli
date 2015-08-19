<?php

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

        if (isset(static::$aliases[$class]))
        {
            if (static::init_class(static::$aliases[$class]))
            {
                class_alias(static::$aliases[$class], $class);
                return true;
            }
            else
            {
                return false;
            }
        }

        if (static::init_class($class))
        {
            return true;
        }

        foreach (static::$aliases as $pattern => $required)
        {
            if (substr($pattern, 0, 1) !== '/')
            {
                continue;
            }

            if (preg_match($pattern, $class, $match))
            {
                $required = str_replace('*', $match[1], $required);
                if (static::init_class($required))
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
     * --
     * @throws \Exception
     *         10 Block already opened.
     *
     * @throws \Exception
     *         20 Block not opened.
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
        Comma means new line
         */
        $use = str_replace(',', "\n", $use);

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
                        "in __use statement for: `{$class}`.", 10
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
                        "in __use statement for: `{$class}`.", 20
                    ));
                }

                $line = $now_line.($line === '}' ? '' : ', ').$line;
                $in_block = false;

                // Go on, this line need to be processed as it would normally be
                // ...
            }

            // Are we in block right now?
            if ($in_block)
            {
                $now_line .= ",".$line;
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
                static::resolve_use_line($line, $namespace)
            );
        }

        // Register all aliases
        foreach ($aliases as $from => $to)
        {
            static::register_alias($from, $to);
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
     * @param string $line
     * @param string $namespace
     * @param string $to
     *        How to alias class, when there's no `->`;
     *        If `null`, then the last segment from a full class name will be used.
     *        This modofiy behaviour when loading sub-classes like this:
     *        .{ exception.foo } // Aliased as exception\foo
     *        .exception.foo     // Aliased as foo
     * --
     * @return array  [ from => as, from => as, from => as ]
     */
    private static function resolve_use_line($line, $namespace, $to=null)
    {
        /*
        Do we have { } ?
         */
        if (strpos($line, '{'))
        {
            // Initiate empty list to be returned
            $list = [];

            // Remove end }
            $line = trim($line, "} \t");

            // Explode by {, getting base and classes
            list($base, $classes) = explode('{', $line, 2);

            // Classes to array
            $classes = explode(',', $classes);

            // Resolve individual classes by calling this method again...
            foreach ($classes as $class)
            {
                $list = array_merge(
                    $list,
                    static::resolve_use_line(
                        $base.trim($class),
                        $namespace,
                        str_replace('.', '\\', trim($class))
                    )
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
            if (!$to)
                $to = substr($line, strrpos($line, '.')+1);
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
     * @param string $from
     * @param string $to
     * --
     * @throws \Exception
     *         10 Alias is already set, cannot rewrite it.
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

        if (isset(static::$aliases[$to]) && static::$aliases[$to] !== $from)
        {
            throw new \Exception(
                "Alias is already set `{$to}`, for `".static::$aliases[$to]."`, ".
                "cannot rewrite it for `{$from}`", 10
            );
        }

        log::debug("Register: `{$from}` => `{$to}`.", __CLASS__);
        static::$aliases[$to] = $from;
    }

    /**
     * Load particular class file, if file exists.
     * Register __use, if set.
     * Call __init, for package, if available and was not called before.
     * Call __init, for class, if available.
     * --
     * @param  string $class
     * --
     * @throws \Exception
     *         10 File was loaded, but class was not found.
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
        Remove left backslash
         */
        $class = ltrim($class, '\\');

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
            $abspath = 'phar://'.MYSLI_BINPATH."/{$package}.phar";
        }
        else
        {
            $abspath = MYSLI_BINPATH."/{$package}";
        }

        /*
        Get class path
         */
        $segments = explode('\\', $class);
        $rootc    = substr_count($package, '.')+1;
        $relpath  = implode('/', array_slice($segments, $rootc)).'.php';

        // If first segment is root,
        // that menas we're loading from root, rather than `lib`
        if (substr($relpath, 0, 5) === 'root/')
            $relpath = substr($relpath, 5);
        else
            $relpath = "lib/{$relpath}";

        // Essamble class filename
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
                "but class: `{$class}` was not found.", 10
            );
        }

        /*
        Resolve use statement if there
         */
        if (defined("{$class}::__use"))
        {
            static::resolve_use(
                $class,
                constant("{$class}::__use")
            );
        }

        /*
        Init this package, if not initialized yet by some other class.
         */
        static::init($package, $abspath);

        /*
        If class has __init method, then call it, to initialize class.
         */
        if (method_exists($class, '__init'))
            call_user_func([$class, '__init']);

        /*
        Register short version of name for this class.
         */
        if ($alias)
        {
            static::register_alias($class, $alias);
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
        /*
        If already initialized, then return.
         */
        if (in_array($package, static::$initialized))
            return;

        log::debug("Initialize: `{$package}`.", __CLASS__);

        /*
        Add self to the initialized list.
         */
        static::$initialized[] = $package;

        /*
        Make class name.
         */
        $class = str_replace('.', '\\', $package).'\\__init';

        /*
        Init class (load file, resolve __use)
         */
        static::init_class($class);
    }
}

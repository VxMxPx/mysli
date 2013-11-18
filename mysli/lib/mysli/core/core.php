<?php

namespace Mysli;

class Core
{
    private static $path;

    /**
     * Base path are required:
     * @param string $pubpath Public URL accessible path,
     *                        where index.php is stored.
     * @param string $libpath Libraries repository.
     * @param string $datpath Data(base) path, where most of the application
     *                        specific files will be stored.
     *                        This path shouldn't be accessible through URL!
     */
    public static function init($pubpath, $libpath, $datpath)
    {
        define('PUBPATH', $pubpath);
        define('LIBPATH', $libpath);
        define('DATPATH', $datpath);

        !defined('CHAR_APOSTROPHE')
            ? define('CHAR_APOSTROPHE', "'")
            : trigger_error('Use of reserved constant: `CHAR_APOSTROPHE`.');

        !defined('CHAR_QUOTE')
            ? define('CHAR_QUOTE', '"')
            : trigger_error('Use of reserved constant: `CHAR_QUOTE`.');

        !defined('CHAR_SPACE')
            ? define('CHAR_SPACE', ' ')
            : trigger_error('Use of reserved constant: `CHAR_SPACE`.');

        !defined('CHAR_SLASH')
            ? define('CHAR_SLASH', '/')
            : trigger_error('Use of reserved constant: `CHAR_SLASH`.');

        !defined('CHAR_BACKSLASH')
            ? define('CHAR_BACKSLASH', '\\')
            : trigger_error('Use of reserved constant: `CHAR_BACKSLASH`.');

        !defined('STRING_CAMELCASE')
            ? define('STRING_CAMELCASE', 'string-camelcase')
            : trigger_error('Use of reserved constant: `STRING_CAMELCASE`.');

        !defined('STRING_UNDERSCORE')
            ? define('STRING_UNDERSCORE', 'string-underscore')
            : trigger_error('Use of reserved constant: `STRING_UNDERSCORE`.');

        self::$path = realpath(dirname(__FILE__));

        // Include core functions
        include(self::$path . DIRECTORY_SEPARATOR . 'functions.php');

        // Load exception files
        $exceptions = [
            'file_system', 'data', 'value'
        ];

        foreach ($exceptions as $exception_file_name) {
            include(ds(self::$path, 'exceptions', $exception_file_name . '.php'));
        }

        // Create alias for itself
        class_alias('Mysli\\Core', 'Core', false);

        // Constructors params (if not in list, only $this will be send)
        $libraries = [
            'Arr'        => [],
            'Str'        => [],
            'Int'        => [],
            'Benchmark'  => [],
            'Event'      => [datpath('core/events.json')],
            'Cfg'        => [datpath('core/cfg.json')],
            'Log'        => [],
            'Cookie'     => [],
            'Librarian'  => [datpath('core/libraries.json')],
            'Error'      => [],
            'Response'   => [],
            'Request'    => [],
            'Language'   => [],
            'Output'     => [],
            'HTML'       => [],
            'Server'     => [],
        ];
        // Those classes won't be aliased
        $keep_namespace = ['Error'];

        // Load all base system classes
        foreach ($libraries as $library => $params) {
            $id = strtolower($library);
            $class = "\\Mysli\\Core\\Lib\\{$library}";

            include(ds(self::$path, "/lib/{$id}.php"));

            if (method_exists($class, 'init')) {
                call_user_func_array([$class, 'init'], $params);
            }
            if (!in_array($library, $keep_namespace)) {
                class_alias(substr($class, 1), $library, false);
            }
        }

        // Set Error Handler
        set_error_handler('Mysli\\Core\\Lib\\Error::handle');

        // Register Class Loader
        spl_autoload_register('Mysli\\Core\\Lib\\Librarian::autoloader');

        // In theory we should have timezone now
        date_default_timezone_set(\Cfg::get('core/timezone', 'UTC'));
        ini_set('display_errors', \Cfg::get('core/debug', false));

        \Benchmark::set_timer('/mysli/core');
        \Log::info('Hello! | PHP version: ' . PHP_VERSION, __FILE__, __LINE__);
        \Event::trigger('/mysli/core::init');
    }

    /**
     * Triger route event.
     * --
     * @return void
     */
    public static function boot()
    {
        \Event::trigger('/mysli/core::boot');
    }

    /**
     * Apply headers and return output as HTML.
     * --
     * @return string
     */
    public static function as_html()
    {
        \Response::apply_headers();
        $output = \Output::as_html();
        \Event::trigger('/mysli/core::as_html', $output);

        return $output;
    }

    /**
     * Trigger the final event.
     */
    public static function terminate()
    {
        // Final event
        \Event::trigger('/mysli/core::terminate');
        exit();
    }
}

<?php

namespace Mysli;

class Core
{
    protected $event;

    /**
     * Base path are required:
     * @param string $pubpath Public URL accessible path,
     *                        where index.php is stored.
     * @param string $libpath Libraries repository.
     * @param string $datpath Data(base) path, where most of the application
     *                        specific files will be stored.
     *                        This path shouldn't be accessible through URL!
     */
    public function __construct($pubpath, $libpath, $datpath)
    {
        define('MYSLI_PUBPATH', $pubpath);
        define('MYSLI_LIBPATH', $libpath);
        define('MYSLI_DATPATH', $datpath);

        $path  = realpath(__DIR__);
        $this->load_exceptions($path);
        $this->load_util(
            $path,
            [
                'Mysli\\Core\\Util\\Arr'  => 'Arr',
                'Mysli\\Core\\Util\\Str'  => 'Str',
                'Mysli\\Core\\Util\\Int'  => 'Int',
                'Mysli\\Core\\Util\\FS'   => 'FS',
                'Mysli\\Core\\Util\\JSON' => 'JSON',
            ]
        );
    }

    /**
     * Init the core libraries, like librarian and config,...
     * --
     * @return void
     */
    public function init()
    {
        // Get Librarian & Register Class Loader
        $librarian = $this->get_librarian();
        spl_autoload_register([$librarian, 'autoloader']);

        // Get Error Handler & Register it
        $error_handler = $librarian->factory('~error_handler');
        set_error_handler([$error_handler, 'handle']);

        $benchmark = $librarian->factory('~benchmarker');
        $benchmark->set_timer('core');

        $log = $librarian->factory('~logger');
        $log->info('Hello! | PHP Version: ' . PHP_VERSION, __FILE__, __LINE__);

        $this->event = $librarian->factory('~event');
        $this->event->trigger('/mysli/core:init');
    }

    /**
     * Will get enabled librarian if exists...
     * --
     * @return void
     */
    protected function get_librarian()
    {
        // There should be librarian folder...
        $libid = datpath('librarian/id.json');
        if (!file_exists($libid)) {
            throw new \Core\FileNotFoundException(
                "Could not found librarian ID file: `{$libid}`.", 1
            );
        }
        $lib_info = \JSON::decode_file($libid, true);
        $lib_file = $lib_info['file'];
        if (!file_exists(libpath($lib_file))) {
            throw new \Core\FileNotFoundException(
                "Librarian file not found: `{$lib_file}`.", 2
            );
        }
        if (!class_exists($lib_info['class'], false)) {
            throw new \Core\ValueException(
                "The librarian class not found: `{$lib_info['class']}`.", 1
            );
        }
        return new $lib_info['class'](datpath('librarian/registry.json'));
    }

    /**
     * Load base exception classes.
     * --
     * @param string $path
     */
    protected function load_exceptions($path)
    {
        $files = scandir($path . DIRECTORY_SEPARATOR . 'exceptions');
        $files = array_diff($files, ['.', '..']);
        foreach ($files as $file) {
            include(
                $path .
                DIRECTORY_SEPARATOR .
                'exceptions' .
                DIRECTORY_SEPARATOR .
                $file
            );
            // Alias the exception, this is so that core exceptions can be used
            // globally, without hard-coded dependency on Mysli.
            $base = substr($file, 0, -4); // Cut off the .php part
            $base = str_replace('_', ' ', $base); // Convert _ to spaces
            // Capitalize words, and remove spaces and add Exception part
            $base = str_replace(' ', '', ucwords($base)) . 'Exception';
            class_alias('Mysli\\Core\\'.$base, 'Core\\'.$base, false);
        }
    }

    /**
     * Load base util classes.
     * --
     * @param  string $path
     * @param  array  $libraries
     * --
     * @return void
     */
    protected function load_util($path, array $libraries)
    {
        if (!function_exists('ds')) {
            // Include core functions
            include(
                $path .
                DIRECTORY_SEPARATOR .
                'util' .
                DIRECTORY_SEPARATOR .
                'functions.php'
            );
        }

        foreach ($libraries as $class => $alias) {
            $file = strtolower($class);
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $file);
            $filename = ds($path, 'util', strtolower($alias) . '.php');

            if (!file_exists($filename)) {
                throw new \Mysli\Core\FileNotFoundException(
                    "File not found: '{$filename}'."
                );
            }
            include($filename);
            class_alias($class, $alias, false);
        }
    }

    /**
     * Trigger the final event.
     */
    public function terminate()
    {
        // Final event
        $this->event->trigger('/mysli/core:terminate');
        exit();
    }
}

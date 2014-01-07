<?php

namespace Mysli;

class Core
{
    protected $path; // Core path
    protected $pubpath;
    protected $libpath;
    protected $datpath;

    protected static $instance;

    // Core libraries
    protected $error;
    public $benchmark;
    public $cfg;
    public $event;
    public $language;
    public $librarian;
    public $log;
    public $output;
    public $request;
    public $response;
    public $server;

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
        $this->pubpath = $pubpath;
        $this->libpath = $libpath;
        $this->datpath = $datpath;

        $this->path = realpath(__DIR__);

        $this->load_exceptions();
        $this->load_util([
            'Mysli\\Core\\Util\\Arr'  => 'Arr',
            'Mysli\\Core\\Util\\Str'  => 'Str',
            'Mysli\\Core\\Util\\Int'  => 'Int',
            'Mysli\\Core\\Util\\FS'   => 'FS',
            'Mysli\\Core\\Util\\JSON' => 'JSON',
        ]);
        $this->load_core_libraries([
            'benchmark', 'log', 'cfg', 'librarian', 'event', 'request',
            'response', 'language', 'output', 'server', 'error'
        ]);

        // Set Error Handler
        set_error_handler([$this->error, 'handle']);

        // Register Class Loader
        spl_autoload_register([$this->librarian, 'autoloader']);

        // In theory we should have timezone now
        date_default_timezone_set($this->cfg->get('core/timezone', 'UTC'));
        ini_set('display_errors', $this->cfg->get('core/debug', false));

        $this->benchmark->set_timer('/mysli/core');
        $this->log->info('Hello! | PHP version: ' . PHP_VERSION, __FILE__, __LINE__);

        $this->event->trigger('/mysli/core->__construct');

        self::$instance = $this;
    }

    /**
     * Return absolute libraries path.
     * --
     * @param  string $path
     * --
     * @return string
     */
    public function libpath($path = null) {
        return ds($this->libpath.'/'.$path);
    }

    /**
     * Return absolute public path.
     * --
     * @param  string $path
     * --
     * @return string
     */
    public function pubpath($path = null) {
        return ds($this->pubpath.'/'.$path);
    }

    /**
     * Return absolute datpath path.
     * --
     * @param  string $path
     * --
     * @return string
     */
    public function datpath($path = null) {
        return ds($this->datpath.'/'.$path);
    }

    /**
     * Trigger the final event.
     */
    public function terminate()
    {
        // Final event
        $this->event->trigger('/mysli/core->terminate');
        exit();
    }

    /**
     * Get params for particular library.
     * --
     * @param  string $library
     * --
     * @return array
     */
    protected function get_params($library)
    {
        switch ($library) {
            case 'benchmark':
                return [];

            case 'log':
                return [
                    [],
                    [
                        'event'     => &$this->event,
                        'benchmark' => $this->benchmark,
                    ],
                ];

            case 'cfg':
                return [
                    [
                        'cfgfile' => $this->datpath('core/cfg.json'),
                    ],
                    [],
                ];

            case 'librarian':
                return [
                    [
                        'libfile' => $this->datpath('core/libraries.json'),
                    ],
                    [
                        'core' => $this,
                        'log'  => $this->log,
                        'cfg'  => $this->cfg,
                    ],
                ];

            case 'event':
                return [
                    [
                        'eventfile' => $this->datpath('core/events.json'),
                    ],
                    [
                        'librarian' => $this->librarian,
                    ],
                ];

            case 'request':
                return [];

            case 'response':
                return [
                    [],
                    [
                        'event' => $this->event,
                    ],
                ];

            case 'language':
                return [
                    [],
                    [
                        'log' => $this->log,
                    ],
                ];

            case 'output':
                return [];

            case 'server':
                return [
                    $this->cfg->get('core/server'),
                    [],
                ];

            case 'error':
                return [
                    [],
                    [
                        'log'   => $this->log,
                        'event' => $this->event,
                    ],
                ];
        }
    }

    /**
     * Load base exception classes.
     */
    protected function load_exceptions()
    {
        $files = scandir($this->path . DIRECTORY_SEPARATOR . 'exceptions');
        $files = array_diff($files, ['.', '..']);
        foreach ($files as $file) {
            include(
                $this->path .
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
     * @param  array $libraries
     * --
     * @return void
     */
    protected function load_util(array $libraries)
    {
        if (!function_exists('ds')) {
            // Include core functions
            include(
                $this->path .
                DIRECTORY_SEPARATOR .
                'util' .
                DIRECTORY_SEPARATOR .
                'functions.php'
            );
        }

        foreach ($libraries as $class => $alias) {
            $file = strtolower($class);
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $file);
            $filename = ds($this->path, 'util', strtolower($alias) . '.php');

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
     * Load core libraries.
     * --
     * @param  array  $libraries
     * --
     * @return void
     */
    protected function load_core_libraries(array $libraries)
    {
        foreach ($libraries as $library) {
            $class_name = '\\Mysli\\Core\\Lib\\' . ucfirst($library);
            $filename = ds($this->path, 'lib', $library . '.php');
            if (!file_exists($filename)) {
                throw new \Mysli\Core\FileNotFoundException(
                    "File not found: '{$filename}'."
                );
            }
            include($filename);
            $class = new \ReflectionClass($class_name);
            $this->{$library} = $class->newInstanceArgs($this->get_params($library));
        }
    }

    /**
     * Return this instance.
     * --
     * @return object \Mysli\Core
     */
    public static function instance()
    {
        return self::$instance;
    }
}

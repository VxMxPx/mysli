<?php

namespace Mysli;

class Core
{
    protected $librarian;

    /**
     * Construct new instance of util, check if is inited!
     * --
     * @param  string $datpath data path, - private directory.
     * @param  string $libpath libraries path, - where all libraries are located.
     * --
     * @throws Exception If datpath is not a valid directory. (1)
     * @throws Exception If libpath is not a valid directory. (2)
     */
    public function __construct($datpath, $libpath)
    {
        if (!$datpath || !is_dir($datpath))
            throw new \Exception("Invalid datpath: `{$datpath}`.", 1);

        if (!$libpath || !is_dir($libpath))
            throw new \Exception("Invalid libpath: `{$libpath}`.", 2);

        define('MYSLI_DATPATH', $datpath);
        define('MYSLI_LIBPATH', $libpath);

        $this->init();
    }

    /**
     * Load all exception classes, etc...
     * --
     * @throws NotFoundException If common.php file is not found. (1)
     * @throws NotFoundException If library file not found. (2)
     * --
     * @return void
     */
    protected function init()
    {
        $path = rtrim(__DIR__, DIRECTORY_SEPARATOR);

        // Load exceptions
        $exception_files = scandir($path . DIRECTORY_SEPARATOR . 'exceptions');
        $exception_files = array_diff($exception_files, ['.', '..']);
        foreach ($exception_files as $file) {
            include(
                $path .
                DIRECTORY_SEPARATOR .
                'exceptions' .
                DIRECTORY_SEPARATOR .
                $file
            );
            // Alias the exception, this is so that core exceptions can be used
            // globally, without hard-coded Mysli namespace.
            $base = substr($file, 0, -4); // Cut off the .php part
            $base = str_replace('_', ' ', $base); // Convert _ to spaces
            // Capitalize words, and remove spaces and add Exception part
            $base = str_replace(' ', '', ucwords($base)) . 'Exception';
            class_alias('Mysli\\Core\\'.$base, 'Core\\'.$base, false);
        }

        // Load common functions
        if (!function_exists('ds')) {
            if (!file_exists($path . DIRECTORY_SEPARATOR . 'common.php'))
                throw new \Mysli\Core\NotFoundException("Cannot find: `common.php`.", 1);
            else include $path . DIRECTORY_SEPARATOR . 'common.php';
        }

        $libraries = [
            'Mysli\\Core\\Lib\\Arr'  => 'Core\\Arr',
            'Mysli\\Core\\Lib\\Str'  => 'Core\\Str',
            'Mysli\\Core\\Lib\\Int'  => 'Core\\Int',
            'Mysli\\Core\\Lib\\FS'   => 'Core\\FS',
            'Mysli\\Core\\Lib\\JSON' => 'Core\\JSON',
        ];

        // Load (and alias) libraries
        foreach ($libraries as $class => $alias) {
            $file = strtolower($class);
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $file);
            $filename = ds($path, 'lib', strtolower(substr($alias, strpos($alias, '\\'))) . '.php');

            if (!file_exists($filename)) {
                throw new \Mysli\Core\NotFoundException(
                    "File not found: '{$filename}'.", 2
                );
            }
            include($filename);
            class_alias($class, $alias, false);
        }
    }

    /**
     * Get librarian.
     * --
     * @throws NotFoundException If librarian ID file couldn't be found. (1)
     * @throws NotFoundException If librarian file not found. (2)
     * @throws NotFoundException If librarian class doesn't exists. (3)
     * --
     * @return object ~librarian
     */
    public function librarian()
    {
        if ($this->librarian) return $this->librarian;

        // Get librarian id file path
        $librarian_id_path = datpath('librarian/id.json');
        if (!file_exists($librarian_id_path))
            throw new \Mysli\Core\NotFoundException(
                "Could not found librarian ID file: `{$librarian_id_path}`.", 1
            );

        // Decode file and try to find librarian class file
        $lib_info = \Core\JSON::decode_file($librarian_id_path, true);
        $lib_file = $lib_info['file'];
        if (!file_exists(libpath($lib_file)))
            throw new \Mysli\Core\NotFoundException(
                "Librarian file not found: `{$lib_file}`.", 2
            );
        else include libpath($lib_file);

        // Librarian class should now be available
        if (!class_exists($lib_info['class'], false))
            throw new \Mysli\Core\NotFoundException(
                "The librarian class not found: `{$lib_info['class']}`.", 3
            );

        // Construct librarian class
        $this->librarian = new $lib_info['class']();
        return $this->librarian;
    }
}

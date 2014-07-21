<?php

namespace Mysli\Core;

class Core
{
    private static $pkg_auto_construct_type = 'prevent';
    protected $pkgm;

    /**
     * Construct new instance of util, check if is inited!
     * --
     * @param  string $datpath data path, - private directory.
     * @param  string $pkgpath packages path, - where all packages are located.
     * --
     * @throws Exception If datpath is not a valid directory. (1)
     * @throws Exception If pkgpath is not a valid directory. (2)
     */
    public function __construct($datpath, $pkgpath)
    {
        if (!$datpath || !is_dir($datpath))
            throw new \Exception("Invalid datpath: `{$datpath}`.", 1);

        if (!$pkgpath || !is_dir($pkgpath))
            throw new \Exception("Invalid pkgpath: `{$pkgpath}`.", 2);

        define('MYSLI_DATPATH', $datpath);
        define('MYSLI_PKGPATH', $pkgpath);

        $this->init();
    }

    /**
     * Load all exception classes, etc...
     * --
     * @throws NotFoundException If common.php file is not found. (1)
     * @throws NotFoundException If particular library file not found. (2)
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
                throw new NotFoundException("Cannot find: `common.php`.", 1);
            else include $path . DIRECTORY_SEPARATOR . 'common.php';
        }

        // Traits
        include ds($path, 'pkg/prevent.php');
        include ds($path, 'pkg/singleton.php');

        $libraries = [
            'Mysli\\Core\\Util\\Arr'  => 'Core\\Arr',
            'Mysli\\Core\\Util\\Str'  => 'Core\\Str',
            'Mysli\\Core\\Util\\Int'  => 'Core\\Int',
            'Mysli\\Core\\Util\\FS'   => 'Core\\FS',
            'Mysli\\Core\\Util\\JSON' => 'Core\\JSON',
        ];

        // Load (and alias) libraries
        foreach ($libraries as $class => $alias) {
            $file = strtolower($class);
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $file);
            $filename = ds($path, 'util', strtolower(substr($alias, strpos($alias, '\\'))) . '.php');

            if (!file_exists($filename)) {
                throw new NotFoundException(
                    "File not found: '{$filename}'.", 2
                );
            }
            include($filename);
            class_alias($class, $alias, false);
        }
    }

    /**
     * Get pkgm.
     * This is helper function. @core has no dependency on pkgm, - if pkgm is
     * not enabled, this will throw NotFoundException.
     * --
     * @throws NotFoundException If pkgm ID file couldn't be found. (1)
     * @throws NotFoundException If pkgm file not found. (2)
     * @throws NotFoundException If pkgm class doesn't exists. (3)
     * --
     * @return object @pkgm
     */
    public function pkgm()
    {
        if ($this->pkgm) return $this->pkgm;

        // Get pkgm id file path
        $pkgm_id_path = datpath('pkgm/id.json');
        if (!file_exists($pkgm_id_path))
            throw new NotFoundException(
                "Could not found pkgm ID file: `{$pkgm_id_path}`.", 1
            );

        // Decode file and try to find pkgm class file
        $pkg_info = \Core\JSON::decode_file($pkgm_id_path, true);
        $pkg_file = $pkg_info['file'];
        if (!file_exists(pkgpath($pkg_file)))
            throw new NotFoundException(
                "`pkgm` file not found: `{$pkg_file}`.", 2
            );
        else include pkgpath($pkg_file);

        // pkgm class should now be available
        if (!class_exists($pkg_info['class'], false))
            throw new NotFoundException(
                "The pkgm class not found: `{$pkg_info['class']}`.", 3
            );

        // Construct pkgm class
        return ($this->pkgm = new $pkg_info['class']());
    }
}

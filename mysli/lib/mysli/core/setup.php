<?php

namespace Mysli\Core;

class Setup
{
    protected $pubpath;
    protected $libpath;
    protected $datpath;
    protected $path;

    public function __construct($config = null)
    {
        // Load functions
        if (!function_exists('ds')) {
            include
                __DIR__ .
                DIRECTORY_SEPARATOR .
                'util' .
                DIRECTORY_SEPARATOR .
                'functions.php';
        }

        if (is_array($config)) {
            $this->pubpath = ds($config['pubpath']);
            $this->libpath = ds($config['libpath']);
            $this->datpath = ds($config['datpath']);
        } else {
            $this->pubpath = pubpath();
            $this->libpath = libpath();
            $this->datpath = datpath();
        }
        $this->path = ds(__DIR__);
    }

    public function before_enable()
    {
        // Check if pubpath exists
        if (!is_dir($this->pubpath)) {
            // If not create it
            if (!mkdir($this->pubpath, 0777, true)) {
                throw new \Exception('Cannot create public directory!', 1);
            }
        }
        // Load index.tpl
        $index_contents = file_get_contents(ds($this->path, 'setup', 'index.tpl'));
        // Replace {{LIBPATH}} and {{DATPATH}}
        $ds = DIRECTORY_SEPARATOR;
        $index_contents = str_replace(
            [
                '{{LIBPATH}}',
                '{{DATPATH}}'
            ],
            [
                '/' . str_replace(DIRECTORY_SEPARATOR, '/', \relative_path($this->libpath, $this->pubpath)),
                '/' . str_replace(DIRECTORY_SEPARATOR, '/', \relative_path($this->datpath, $this->pubpath)),
            ],
            $index_contents
        );
        // Create index.php
        file_put_contents(ds($this->pubpath, 'index.php'), $index_contents);

        // Check if datpath exists
        if (!is_dir($this->datpath)) {
            // If not create it
            if (!mkdir($this->datpath, 0777, true)) {
                throw new \Exception('Cannot create data directory!', 2);
            }
        }

        // Check if core folder exists...
        if (!is_dir(ds($this->datpath, 'core'))) {
            if (!mkdir(ds($this->datpath, 'core'))) {
                throw new \Exception('Cannot create `core` directory.', 3);
            }
        }

        // Save core ID!
        file_put_contents(
            ds($this->datpath, '/core/id.json'),
            json_encode([
                'file'  => 'mysli/core/core.php',
                'class' => 'Mysli\\Core',
            ])
        );

        return true;
    }

    public function after_enable()
    { return true; }

    public function before_disable()
    { return true; }

    public function after_disable()
    {
        \FS::dir_remove($this->datpath);
        \FS::dir_remove($this->pubpath);
        return true;
    }
}

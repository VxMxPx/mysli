<?php

namespace Mysli\Core;

class Setup
{
    protected $pubpath;
    protected $libpath;
    protected $datpath;
    protected $path;

    public function __construct($pubpath, $libpath, $datpath)
    {
        // Load functions
        if (!function_exists('ds')) {
            include __DIR__ . DIRECTORY_SEPARATOR . 'functions.php';
        }

        $this->pubpath = ds($pubpath);
        $this->libpath = ds($libpath);
        $this->datpath = ds($datpath);
        $this->path    = ds(__DIR__);
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
                '/' . str_replace(DIRECTORY_SEPARATOR, '/', relative_path($this->libpath, $this->pubpath)),
                '/' . str_replace(DIRECTORY_SEPARATOR, '/', relative_path($this->datpath, $this->pubpath)),
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
        // Create 'core' folder
        if (!mkdir(ds($this->datpath, 'core'), 0777)) {
            throw new \Exception('Cannot create core directory!', 3);

        }
        file_put_contents(
            ds($this->datpath, 'core', 'dot.json'),
            file_get_contents(ds($this->path, 'setup', 'dot.json'))
        );
        file_put_contents(
            ds($this->datpath, 'core', 'cfg.json'),
            file_get_contents(ds($this->path, 'setup', 'cfg.json'))
        );
        file_put_contents(
            ds($this->datpath, 'core', 'events.json'),
            '{}'
        );
        $meta = json_decode(file_get_contents(ds($this->path, 'meta.json')), true);
        $meta['required_by'] = [];
        $meta = ['mysli/core' => $meta];
        file_put_contents(
            ds($this->datpath, 'core', 'libraries.json'),
            json_encode($meta)
        );

        // Load dot.tpl
        $dot_contents = file_get_contents(ds($this->path, 'setup', 'dot.tpl'));
        // Replace {{LIBPATH}} and {{PUBPATH}}
        $ds = DIRECTORY_SEPARATOR;
        $dot_contents = str_replace(
            [
                '{{LIBPATH}}',
                '{{PUBPATH}}'
            ],
            [
                '/' . str_replace(DIRECTORY_SEPARATOR, '/', relative_path($this->libpath, $this->datpath)),
                '/' . str_replace(DIRECTORY_SEPARATOR, '/', relative_path($this->pubpath, $this->datpath)),
            ],
            $dot_contents
        );
        // Create index.php
        file_put_contents(ds($this->datpath, 'dot'), $dot_contents);

        return true;
    }

    public function after_enable()
    { return true; }

    public function before_disable()
    { return true; }

    public function after_disable()
    { return true; }
}

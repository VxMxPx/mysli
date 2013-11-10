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
        $this->pubpath = rtrim($pubpath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->libpath = rtrim($libpath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->datpath = rtrim($datpath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->path    = rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
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
        $index_contents = file_get_contents($this->path . 'setup' . DIRECTORY_SEPARATOR . 'index.tpl');
        // Replace {{LIBPATH}} and {{DATPATH}}
        $index_contents = str_replace(
            ['{{LIBPATH}}', '{{DATPATH}}'],
            [$this->libpath, $this->datpath],
            $index_contents
        );
        // Create index.php
        file_put_contents($this->pubpath . 'index.php', $index_contents);

        // Check if datpath exists
        if (!is_dir($this->datpath)) {
            // If not create it
            if (!mkdir($this->datpath, 0777, true)) {
                throw new \Exception('Cannot create data directory!', 2);
            }
        }
        // Create 'core' folder
        if (!mkdir($this->datpath . 'core', 0777)) {
            throw new \Exception('Cannot create core directory!', 3);

        }
        file_put_contents(
            $this->datpath . 'core' . DIRECTORY_SEPARATOR . 'cfg.json',
            file_get_contents($this->path . 'setup' . DIRECTORY_SEPARATOR . 'cfg.json')
        );
        file_put_contents(
            $this->datpath . 'core' . DIRECTORY_SEPARATOR . 'events.json',
            '{}'
        );
        $meta = json_decode(file_get_contents($this->path . 'meta.json'), true);
        $meta['required_by'] = [];
        $meta = ['mysli/core' => $meta];
        file_put_contents(
            $this->datpath . 'core' . DIRECTORY_SEPARATOR . 'libraries.json',
            json_encode($meta)
        );

        return true;
    }

    public function after_enable()
    { return true; }

    public function before_disable()
    { return true; }

    public function after_disable()
    { return true; }
}

<?php

namespace Mysli\Core;

class Setup
{
    protected $pkgpath;
    protected $datpath;
    protected $path;

    // Config is not automatically injected, as CORE has no dependencies!
    public function __construct(array $config = null)
    {
        // Load common functions
        if (!function_exists('ds')) {
            include __DIR__ . DIRECTORY_SEPARATOR . 'common.php';
        }

        if (is_array($config)) {
            $this->pkgpath = ds($config['pkgpath']);
            $this->datpath = ds($config['datpath']);
        } else {
            $this->pkgpath = pkgpath();
            $this->datpath = datpath();
        }
        $this->path = ds(__DIR__);
    }

    public function before_enable()
    {
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
        return true;
    }
}

<?php

namespace Mysli\Web;

class Setup
{
    protected $config;
    protected $event;

    /**
     * Setup Web
     * --
     * @param object $config ~config
     * @param object $event  ~event
     */
    public function __construct($config, $event)
    {
        $this->config = $config;
        $this->event = $event;
    }

    public function before_enable()
    {
        // Public path!
        $pubpath = datpath('/../public');
        \Core\FS::dir_create($pubpath);
        $pubpath = realpath($pubpath);

        if (!$pubpath) {
            throw new \Core\FSException('Public directory couldn\'t be created.', 1);
        }

        $this->config->merge([
            'url'           => null,
            'relative_path' => relative_path($pubpath, datpath())
        ]);

        $this->config->write();

        // Load index.tpl
        $index_contents = file_get_contents(ds(__DIR__, '/setup/index.tpl'));

        // Replace {{LIBPATH}} and {{PUBPATH}}
        $index_contents = str_replace(
            [
                '{{LIBPATH}}',
                '{{DATPATH}}',
            ],
            [
                '/' . str_replace(DIRECTORY_SEPARATOR, '/', relative_path(libpath(), $pubpath)),
                '/' . str_replace(DIRECTORY_SEPARATOR, '/', relative_path(datpath(), $pubpath)),
            ],
            $index_contents
        );

        // Register final event
        $this->event->register('mysli/web/index:done', 'mysli/web::output');

        // Create index.php
        return !!(file_put_contents(ds($pubpath, 'index.php'), $index_contents));
    }

    public function after_enable()
    { return true; }

    public function before_disable()
    { return true; }

    public function after_disable()
    {
        $this->config->destroy();
        \Core\FS::dir_remove(ds(datpath(), '/../public'));
        $this->event->unregister('mysli/web/index:done', 'mysli/web::output');

        return true;
    }
}

<?php

namespace Mysli\Web;

class Setup
{
    protected $config;
    protected $event;
    protected $ecsi;

    /**
     * Setup Web
     * --
     * @param object $config
     * @param object $event
     * @param object $csi
     */
    public function __construct($config, $event, $csi)
    {
        $this->config = $config;
        $this->event = $event;

        $this->dcsi = new $csi('mysli/web/disable');
        $this->dcsi->hidden('remove_data');
        $this->dcsi->hidden('remove_config');

        $this->ecsi = new $csi('mysli/web/enable');
        $this->ecsi->input(
            'relative_path',
            'Public path (relative to: ' . datpath() . ')',
            '../public',
            function (&$field) {
                if (strpos($field['value'], '..')) {
                    $field['value'] = datpath($field['value']);
                }
                return true;
            }
        );
    }

    /**
     * Before enable
     * --
     * @return mixed ~csi or true
     */
    public function before_enable()
    {
        // CSI needs to be successful before we can continue
        if ($this->ecsi->status() !== 'success') return $this->ecsi;

        // Create public directory
        $pubpath = $this->ecsi->get('relative_path');
        \Core\FS::dir_create($pubpath);
        $pubpath = realpath($pubpath);
        if (!$pubpath)
            throw new \Core\FSException('Public directory couldn\'t be created.', 1);

        // Create config file
        $this->config->merge([
            'url'           => null,
            'relative_path' => relative_path($pubpath, datpath())
        ]);
        $this->config->write();

        // Create index file
        $index_contents = file_get_contents(ds(__DIR__, '/setup/index.tpl'));
        $index_contents = str_replace(
            [
                '{{PKGPATH}}',
                '{{DATPATH}}',
            ],
            [
                '/' . str_replace(DIRECTORY_SEPARATOR, '/', relative_path(pkgpath(), $pubpath)),
                '/' . str_replace(DIRECTORY_SEPARATOR, '/', relative_path(datpath(), $pubpath)),
            ],
            $index_contents
        );
        file_put_contents(ds($pubpath, 'index.php'), $index_contents);

        // Register events
        $this->event->register('mysli/web/index:done', 'mysli/web->output');
        $this->event->register('mysli/web/index:start', 'mysli/web->route');

        return true;
    }

    /**
     * After disable
     * --
     * @return null
     */
    public function after_disable()
    {
        // Unregister events
        $this->event->unregister('mysli/web/index:done', 'mysli/web->output');
        $this->event->unregister('mysli/web/index:start', 'mysli/web->route');
    }
}

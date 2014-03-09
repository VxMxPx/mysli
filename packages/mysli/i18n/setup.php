<?php

namespace Mysli\I18n;

class Setup
{
    protected $dcsi;
    protected $config;

    public function __construct($csi, $config)
    {
        $this->config = $config;

        $this->dcsi = new $csi('mysli/i18n/disable');
        $this->dcsi->hidden('remove_data');
        $this->dcsi->hidden('remove_config');
    }

    // ENABLE ------------------------------------------------------------------

    public function before_enable()
    {
        \Core\FS::dir_create(datpath('i18n'), \Core\FS::EXISTS_MERGE);

        $this->config->merge([
            'primary_language'  => 'en-us',
            'fallback_language' => 'en'
        ]);
        $this->config->write();

        return true;
    }

    // DISABLE -----------------------------------------------------------------

    public function before_disable()
    {
        if ($this->dcsi->status() !== 'success') return $this->dcsi;
        else return true;
    }

    public function after_disable()
    {
        // Remove public directory
        if ($this->dcsi->get('remove_data')) {
            \Core\FS::dir_remove(datpath('i18n'));
        }

        // Remove configurations
        if ($this->dcsi->get('remove_config')) {
            $this->config->destroy();
        }

        return true;
    }
}

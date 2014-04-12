<?php

namespace Mysli\I18n;

class Setup
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    // ENABLE ------------------------------------------------------------------

    public function before_enable()
    {
        \Core\FS::dir_create(datpath('mysli.i18n'), \Core\FS::EXISTS_MERGE);

        $this->config->merge([
            'primary_language'  => 'en',
            'secondary_language' => null
        ]);
        $this->config->write();

        return true;
    }
}

<?php

namespace Mysli\Session;

class Setup
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function before_enable()
    {
        $this->config->merge([
            'cookie_name'        => 'mysli_session',
            'require_ip'         => false,
            'require_agent'      => false,
            'expires'            => 60 * 60 * 24 * 7,
            'change_id_on_renew' => false,
        ]);
        $this->config->write();
        \FS::dir_create(datpath('session'), \FS::EXISTS_MERGE);
        return true;
    }

    public function before_disable()
    {
        $this->config->destroy();
        return \FS::dir_remove(datpath('session'));
    }
}

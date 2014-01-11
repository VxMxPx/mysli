<?php

namespace Mysli\Session;

class Setup
{
    protected $core;

    public function __construct(array $config = [], array $dependencies = [])
    {
        $this->core = $dependencies['core'];
    }

    public function before_enable()
    {
        $config['mysli']['session'] = [
            'cookie_name'        => 'mysli_session',
            'require_ip'         => false,
            'require_agent'      => false,
            'expires'            => 60 * 60 * 24 * 7,
            'change_id_on_renew' => false,
        ];
        $this->core->cfg->append($config);
        $this->core->cfg->write();
        \FS::dir_create(datpath('session'), \FS::EXISTS_MERGE);
        return true;
    }

    public function before_disable()
    {
        $this->core->cfg->set('mysli/session', null);
        $this->core->cfg->write();
        return \FS::dir_remove(datpath('session'));
    }
}

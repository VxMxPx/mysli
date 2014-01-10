<?php

namespace Mysli\Cookie;

class Setup
{
    protected $core;

    public function __construct(array $config = [], array $dependencies = [])
    {
        $this->core = $dependencies['core'];
    }

    public function before_enable()
    {
        $config['mysli']['cookie'] = [
            'timeout' => 60 * 60 * 7, // 7 Days
            'domain'  => '', // Dynamic
            'prefix'  => 'mysli_',
        ];

        $this->core->cfg->append($config);
        $this->core->cfg->write();
        return true;
    }

    public function before_disable()
    {
        $this->core->cfg->set('mysli/cookie', null);
        $this->core->cfg->write();
        return true;
    }
}

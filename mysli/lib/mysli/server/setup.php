<?php

namespace Mysli\Server;

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
            'url' => null
        ]);

        return $this->config->write();
    }

    public function before_disable()
    {
        $this->config->destroy();
    }
}

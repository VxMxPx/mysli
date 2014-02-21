<?php

namespace Mysli\Cookie;

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
            'timeout' => 60 * 60 * 24 * 7, // 7 Days
            'domain'  => '', // Dynamic
            'prefix'  => 'mysli_',
        ]);

        return $this->config->write();
    }

    public function before_disable()
    {
        $this->config->destroy();
    }
}

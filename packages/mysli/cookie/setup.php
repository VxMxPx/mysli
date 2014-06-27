<?php

namespace Mysli\Cookie;

class Setup
{
    protected $config;

    public function __construct(\Mysli\Config $config)
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
}

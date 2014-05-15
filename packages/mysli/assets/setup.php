<?php

namespace Mysli\Assets;

class Setup
{
    private $event;
    private $config;

    public function __construct($event, $config)
    {
        $this->event = $event;
        $this->config = $config;
    }

    public function after_enable()
    {
        $this->config->merge([
            'debug' => false
        ]);
        $this->config->write();

        $this->event->register(
            'mysli/tplp/tplp:instantiated',
            'mysli/assets->register'
        );
    }

    public function before_disable()
    {
        $this->event->unregister(
            'mysli/tplp/tplp:instantiated',
            'mysli/assets->register'
        );
    }
}

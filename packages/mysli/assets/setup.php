<?php

namespace Mysli\Assets;

class Setup
{
    private $event;
    private $config;

    public function __construct(\Mysli\Event\Event $event,
                                \Mysli\Config\Config $config)
    {
        $this->event = $event;
        $this->config = $config;
    }

    public function after_enable()
    {
        $this->config->merge(
            \Core\JSON::decode_file(ds(__DIR__, 'config.json'), true));
        $this->config->write();

        $this->event->register(
            'mysli/tplp/tplp:instantiated',
            'mysli/assets/service->register'
        );
    }

    public function before_disable()
    {
        $this->event->unregister(
            'mysli/tplp/tplp:instantiated',
            'mysli/assets/service->register'
        );
    }
}

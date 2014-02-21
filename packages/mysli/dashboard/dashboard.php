<?php

namespace Mysli;

class Dashboard
{
    protected $web;
    protected $output;

    public function __construct($web, $router, $event, $output)
    {
        $this->web = $web;
        $this->output = $output;
    }

    public function display($response)
    {
        $response->status_200_ok();
        $this->output->add(
            '<h1>Hi! I\'m a Dashboard!</h1>',
            'mysli/dashboard/display'
        );
    }
}

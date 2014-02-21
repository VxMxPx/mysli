<?php

namespace Mysli;

class Router
{
    protected $event;
    protected $request;
    protected $response;

    public function __construct($event, $request, $response)
    {
        $this->event = $event;
        $this->request = $request;
        $this->response = $response;
    }

    public function route()
    {
        // Get route and remove any * < > character.
        $route = implode('/', $this->request->segments());
        $route = str_replace(['*', '<', '>'], '', $route);
        // Get method (post,delete,put,get)
        $method = strtolower($this->request->get_method());

        // Events...
        $this->event->trigger(
            "mysli/router/route:{$method}<{$route}>",
            [$this->response]
        );

        if ($this->response->get_status() === 0) {
            $this->response->status_404_not_found();
        }

        if ($this->response->get_status() === 404) {
            $this->event->trigger('mysli/router/route:404');
        }
    }
}

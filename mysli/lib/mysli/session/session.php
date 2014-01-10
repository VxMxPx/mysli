<?php

namespace Mysli;

class Session
{
    protected $core;
    protected $cookie;
    protected $users;

    public function __construct(array $config = [], array $dependencies = [])
    {
        $this->core   = $dependencies['core'];
        $this->cookie = $dependencies['cookie'];
        $this->users  = $dependencies['users'];
    }
}

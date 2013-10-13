<?php

namespace Mysli\Core;

class Setup
{
    public function __construct()
    { return true; }

    public function before_enable()
    { return true; }

    public function after_enable()
    { return true; }

    public function before_disable()
    { return true; }

    public function after_disable()
    { return true; }
}
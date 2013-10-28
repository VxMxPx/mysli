<?php

namespace Mysli;

class Backend
{
    public function __construct($dependencies)
    {
    }

    public function display()
    {
        \Output::add('Yay!');
    }
}
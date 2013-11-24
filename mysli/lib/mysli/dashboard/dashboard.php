<?php

namespace Mysli;

class Dashboard
{
    public function __construct($dependencies)
    {
    }

    public function display()
    {
        \Output::add('Yay!');
    }
}

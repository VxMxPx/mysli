<?php

namespace Mysli;

class Backend
{
    public function say_hi()
    {
        return 'hi';
    }

    public function say_number($number)
    {
        return 'The random number is: ' . $number;
    }
}

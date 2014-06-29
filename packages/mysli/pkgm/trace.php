<?php

namespace Mysli\Pkgm;

class Trace
{
    private $trace = [];

    public function __construct(array $trace)
    {
        $this->trace = $trace;
    }

    public function get($index, $position = 0)
    {
        $selected = array_slice($this->trace, $index, 1)[0];

        if (is_array($selected) && isset($selected[$position])) {
            return $selected[$position];
        }
    }

    public function get_current()
    {
        return $this->get(-1);
    }

    public function get_last()
    {
        return $this->get(-2);
    }

    public function get_all()
    {
        return $this->trace;
    }
}

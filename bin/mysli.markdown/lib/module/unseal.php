<?php

namespace mysli\markdown\module; class unseal extends std_module
{
    function process($at)
    {
        while ($this->lines->has($at))
        {
            $this->lines->set($at, $this->unseal($at));
            $at++;
        }
    }
}

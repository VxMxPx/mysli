<?php

/**
 * Unseal previously sealed lines.
 */
namespace mysli\markdown\module; class unseal extends std_module
{
    /**
     * --
     * @param integer $at
     */
    function process($at)
    {
        while ($this->lines->has($at))
        {
            $this->lines->set($at, $this->unseal($at));
            $at++;
        }
    }
}

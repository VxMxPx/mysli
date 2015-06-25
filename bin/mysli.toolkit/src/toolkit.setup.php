<?php

namespace mysli\toolkit; class toolkit_setup
{
    /**
     * When toolkit is enabled, default folders and files need to be created.
     * Toolkit setup cannot have any dependencies, and at this point toolkit
     * is not initialized yed.
     * --
     * @param  string $apppath Absolute application root path.
     * @param  string $binpath Absolute binaries root path.
     * @param  string $pubpath Absolute public path.
     * --
     * @return boolean
     */
    static function enable($apppath, $binpath, $pubpath)
    {

    }
}

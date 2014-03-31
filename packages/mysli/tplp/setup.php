<?php

namespace Mysli\Tplp;

class Setup
{
    protected $dcsi;

    public function __construct($csi)
    {
        $this->dcsi = new $csi('mysli/tplp/disable');
        $this->dcsi->hidden('remove_data');
    }

    // ENABLE ------------------------------------------------------------------

    public function before_enable()
    {
        \Core\FS::dir_create(datpath('tplp'), \Core\FS::EXISTS_MERGE);
        return true;
    }

    // DISABLE -----------------------------------------------------------------

    public function before_disable()
    {
        if ($this->dcsi->status() !== 'success') return $this->dcsi;
        else return true;
    }

    public function after_disable()
    {
        // Remove public directory
        if ($this->dcsi->get('remove_data')) {
            \Core\FS::dir_remove(datpath('tplp'));
        }

        return true;
    }
}

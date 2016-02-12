<?php

/**
 * Create horizontal rule (hr) from --- or ___.
 */
namespace mysli\markdown\module; class rule extends std_module
{
    /**
     * --
     * @param integer $at
     */
    function process($at)
    {
        $regbag = [
            '/^\s*(-|_|\*){3,}\s*$/' => '<hr/>',
        ];

        $this->process_inline($regbag, $at, [
            'no-process' => true
        ]);
    }
}

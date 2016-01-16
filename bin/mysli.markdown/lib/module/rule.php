<?php

namespace mysli\markdown\module; class rule extends std_module
{
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

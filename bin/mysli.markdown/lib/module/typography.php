<?php

namespace mysli\markdown\module; class typography extends std_module
{
    function process($at)
    {
        $regbag = [
            '/(\(c\))/i'  => '&copy;',   // ©
            '/(\(r\))/i'  => '&reg;',    // ®
            '/(\(tm\))/i' => '&trade;',  // ™
            '/(\(p\))/i'  => '&sect;',   // §
            '/(\+\-)/'    => '&plusmn;', // ±
            '/(\.{2,})/'  => '&hellip;', // …
            '/(\-{3})/'   => '&mdash;',  // —
            '/(\-{2})/'   => '&ndash;',  // –

            '/(\!{3,})/' => '!!!',
            '/(\?{3,})/' => '???',
        ];

        $regbag_multi = [
            "/(?<=\s|^)(')([^\s].*?)(')(?<![\s$])/m" => '&lsquo;$2&rsquo;', // ‘...’
            "/(?<!\s|^)(')/m"                        => '&rsquo;',          // ...’.
            '/(?<=\s|^)(")([^\s].*?)(")(?<![\s$])/m' => '&ldquo;$2&rdquo;', // “...”
            '"'                                      => '&quot;',           // "...
        ];

        $this->process_inline($regbag, $at);
        $this->process_inline_multi($regbag_multi, $at);
    }
}

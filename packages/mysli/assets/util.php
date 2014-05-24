<?php

namespace Mysli\Assets;

trait Util
{
    /**
     * Convert file ext to supported format.
     * styl => css
     * --
     * @param  string $file
     * --
     * @return string
     */
    private function convert_ext($file)
    {
        $ext = \Core\FS::file_extension($file);
        $list = $this->config->get('ext', []);

        if (isset($list[$ext])) {
            return substr($file, 0, -(strlen($ext))) . $list[$ext];
        }

        return $file;
    }
}

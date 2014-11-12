<?php

namespace mysli\web\ui;

__use(__namespace__, '
    mysli/framework/fs/fs,file

    mysli/util/tplp
    mysli/util/output

    mysli/web/response
    mysli/web/request
');

class ui {
    static function developer() {

        $script = request::get('script', 'index');

        response::set_status(200);
        output::add(
            tplp::select(
                'mysli/web/ui',
                'ui',
                [
                    'script' => self::get_script($script),
                    'page'   => $script
                ]
            )
        );
    }

    private static function get_script($script) {
        $file = fs::pkgpath('mysli/web/ui/tplp/scripts/', $script.'.js');
        if (file::exists($file)) {
            return file::read($file);
        }
    }
}

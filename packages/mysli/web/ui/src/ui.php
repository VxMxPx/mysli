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

        $template = tplp::select('mysli/web/ui');

        if ($html = request::get('html')) {
            if (!$template->has($html)) {
                response::set_status(404);
                output::add("Template not found: `{$html}`");
            } else {
                response::set_status(200);
                output::add($template->render($html));
            }
        } elseif ($js = request::get('js')) {
            if ($js != preg_replace('/[^a-z]/i', '', $js)) {
                response::set_status(400);
                output::add("Bad request: `{$js}`");
            } else {
                $file = fs::pkgpath('mysli/web/ui/tplp/scripts/', $js.'.js');
                if (!file::exists($file)) {
                    response::set_status(404);
                    output::add("File not found: `{$js}`");
                } else {
                    response::set_status(200);
                    output::add(file::read($file));
                }
            }
        } else {
            response::set_status(200);
            $script = request::get('script', 'index');
            output::add(
                $template->render(
                    'ui',
                    [
                        'script' => self::get_script($script),
                        'page'   => $script
                    ]
                )
            );
        }
    }

    private static function get_script($script) {
        $file = fs::pkgpath('mysli/web/ui/tplp/scripts/', $script.'.js');
        if (file::exists($file)) {
            return file::read($file);
        }
    }
}

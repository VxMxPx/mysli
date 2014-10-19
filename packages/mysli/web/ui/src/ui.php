<?php

namespace mysli\web\ui;

__use(__namespace__,
    'mysli/framework/fs',
    'mysli/web/response',
    'mysli/web/request',
    'mysli/web/web',
    'mysli/util/tplp',
    'mysli/util/output'
);

class ui {
    static function examples() {
        response::status_200_ok();
        $route = request::segment(1) ?: 'alerts';

        $template = tplp::select('mysli/web/ui');
        $template->set_variable('get_alt', self::get_alt());
        $template->set_variable('get_alt_invert', self::get_alt(true));
        $template->set_variable('alt_link', self::alt_link($route));
        $template->set_variable('get_navigation', function () use ($route) {
            $files = fs::ls(fs::ds('mysli/web/ui/tplp'));
            $links = [];

            foreach ($files as $file) {

                if (substr($file, -5) !== '.tplm' || $file === 'index.tplm') {
                    continue;
                }

                $clean = substr($file, 0, -5);

                if ($clean === $route) {
                    $links[] = '<strong>' . ucfirst($clean) . '</strong>';
                } else {
                    $links[] = '<a href="'.web::url('mysli-ui-examples/'.
                                $clean).'">'.ucfirst($clean).'</a>';
                }
            }
            return implode(' | ', $links);
        });

        output::add($template->render($route, ['title' => ucfirst($route)]));
    }

    private static function get_alt($double=false) {

        if (request::get('alt') === 'true') {
            $alt = true;
        } else {
            $alt = false;
        }

        $alt = $double ? !$alt : $alt;
        return ($alt ? 'alt' : '');
    }
    private static function alt_link($uri) {
        $query = ['alt' => request::get('alt') === 'true' ? 'false' : 'true' ];
        $url = web::url(request::modify_query($query));
        return '<a href="'.$url.'mysli-ui-examples/'.$uri.'/">Inverse</a>';
    }
}

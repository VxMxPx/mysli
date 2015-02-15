<?php

namespace mysli\cms\dash\tplp;

__use(__namespace__, '
    mysli.web.web
');

class util {
    static function url($url=null) {
        return web::url($url.'/dashboard/'.$url);
    }
}

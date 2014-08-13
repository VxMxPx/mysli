<?php

namespace mysli\assets {

    use mysli\config as config;
    use mysli\event as event;
    use mysli\json as json;
    use mysli\fs as fs;

    class setup {
        static function enable() {
            $defaults = json::decode_file(
                fs::ds(__DIR__, '../data/config.json'));
            config::select('mysli/assets')
                ->merge($defaults)
                ->save();
            event::register(
                'mysli/tplp/tplp:instantiated',
                'mysli/assets/service::register');
            return true;
        }
        static function disable() {
            event::unregister('mysli/assets/service::register');
            return true;
        }
    }
}

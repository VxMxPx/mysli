<?php

namespace mysli\assets\setup {

    \inject::to(__namespace__)
    ->from('mysli/config')
    ->from('mysli/event')
    ->from('mysli/json')
    ->from('mysli/fs');

    function enable() {
        $defaults = json::decode_file(
            fs::pkgpath('mysli/assets/data/config.json'));

        config::select('mysli/assets')
            ->merge($defaults)
            ->save();

        event::register(
            'mysli/tplp/tplp:instantiated',
            'mysli/assets/service::register');

        return true;
    }
    function disable() {
        event::unregister('mysli/assets/service::register');
        return true;
    }
}

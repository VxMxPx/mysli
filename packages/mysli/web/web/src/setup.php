<?php

namespace mysli\web\web\setup;

__use(__namespace__,
    './web',
    'mysli/framework/csi',
    'mysli/framework/event',
    'mysli/framework/config',
    'mysli/framework/fs/{fs,file,dir}'
);

function enable() {
    $csi = new csi('mysli/web/web/enable');
    $csi->input(
        'relative_path',
        'Public path (relative to: ' . fs::datpath() . ')',
        '../public',
        function (&$field) {
            if (strpos($field['value'], '..')) {
                $field['value'] = fs::datpath($field['value']);
            }
            return true;
        }
    );
    if ($csi->status() !== 'success') {
        return $csi;
    }

    $pubpath = $csi->get('relative_path');
    if (!dir::create($pubpath)) {
        return false;
    }

    $c = config::select('mysli/web/web');
    $c->merge([
        'url'           => null,
        'relative_path' => fs::relative_path($pubpath, fs::datpath())
    ]);
    if (!$c->save()) {
        return false;
    }

    $index_contents = file::read(fs::ds(__DIR__, 'data/index.html'));
    $index_contents = str_replace(
        [
            '{{PKGPATH}}',
            '{{DATPATH}}',
        ],
        [
            '/' . str_replace(DIRECTORY_SEPARATOR,
                                '/',
                                fs::relative_path(fs::pkgpath(), $pubpath)),
            '/' . str_replace(DIRECTORY_SEPARATOR,
                                '/',
                                fs::relative_path(fs::datpath(), $pubpath)),
        ],
        $index_contents
    );

    if (!file::write(fs::ds($pubpath, 'index.php'), $index_contents)) {
        return false;
    }

    event::register('mysli/web/web/index:start', 'mysli\\web\\web::route');
    event::register('mysli/web/web/index:done',  'mysli\\web\\web::output');

    return true;
}
function disable() {
    $c = config::select('mysli/web/web');
    event::unregister('mysli/web/web/index:start', 'mysli\\web\\web::route');
    event::unregister('mysli/web/web/index:done',  'mysli\\web\\web::output');

    return dir::remove(web::path())
        && $c->destroy();
}


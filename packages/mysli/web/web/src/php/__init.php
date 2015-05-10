<?php

namespace mysli\web\web;

__use(__namespace__, '
    mysli.util.config
    mysli.web.web
    mysli.framework.csi
    mysli.framework.event
    mysli.framework.fs/fs,file,dir
');

class __init
{
    private static $events = [
        'mysli.web.web/index:start' => 'mysli\web\web::route',
        'mysli.web.web/index:done'  =>  'mysli\web\web::output'
    ];

    static function enable($csi=null)
    {
        if (!$csi)
        {
            $csi = new csi('mysli.web.web/enable');
            $csi->input(
                'relative_path',
                'Public path (relative to: ' . fs::datpath() . ')',
                '../public',
                function (&$field)
                {
                    if (substr($field['value'], 0, 2) === '..')
                    {
                        $field['value'] = fs::datpath($field['value']);
                    }

                    return true;
                }
            );
        }

        if ($csi->status() !== 'success')
        {
            return $csi;
        }

        $pubpath = $csi->get('relative_path');

        if (!dir::create($pubpath))
        {
            return false;
        }

        $pubpath = realpath($pubpath);

        $c = config::select('mysli.web.web');
        $c->merge([
            'url'           => null,
            'relative_path' => fs::relative_path($pubpath, fs::datpath())
        ]);

        if (!$c->save())
        {
            return false;
        }

        $index_contents = file::read(
            fs::pkgroot(__DIR__, 'data/index.html')
        );
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

        if (!file::write(fs::ds($pubpath, 'index.php'), $index_contents))
        {
            return false;
        }

        event::register(self::$events);

        return true;
    }

    static function disable()
    {
        $c = config::select('mysli.web.web');
        event::unregister(self::$events);

        return dir::remove(web::path())
            && $c->destroy();
    }
}

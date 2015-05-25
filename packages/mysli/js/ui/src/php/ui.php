<?php

namespace mysli\js\ui;

__use(__namespace__, '
    mysli.framework.fs/fs,file
    mysli.util.tplp
    mysli.util.output
    mysli.web.response
    mysli.web.request
');

const scrdir = 'data/scripts';

class ui
{
    static function developer()
    {
        $template = tplp::select('mysli.js.ui');

        if ($html = request::get('html'))
        {
            if (!$template->has($html))
            {
                response::set_status(404);
                output::add("Template not found: `{$html}`");
            }
            else
            {
                response::set_status(200);
                output::add($template->render($html));
            }
        }
        elseif ($js = (request::get('js') ?: request::get('src')))
        {
            if ($js != preg_replace('/[^a-z_]/i', '', $js))
            {
                response::set_status(400);
                output::add("Bad request: `{$js}`");
            }
            else
            {
                $file = fs::pkgroot(__DIR__, scrdir, $js.'.js');

                if (!file::exists($file))
                {
                    response::set_status(404);
                    output::add("File not found: `{$js}`");
                }
                else
                {
                    response::set_status(200);
                    output::add(file::read($file));
                }
            }
        }
        elseif ($doc = request::get('doc'))
        {
            if ($doc != preg_replace('/[^a-z_]/i', '', $doc))
            {
                response::set_status(400);
                output::add("Bad request: `{$doc}`");
            }
            else
            {
                $file = fs::pkgroot(__DIR__, "doc/README.{$doc}.md");
                response::set_status(200);

                if (!file::exists($file))
                {
                    output::add(
                        "<p>No documentation available for this component at this time.</p>"
                    );
                }
                else
                {
                    output::add(file::read($file));
                }
            }
        }
        else
        {
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

    private static function get_script($script)
    {
        $file = fs::pkgroot(__DIR__, scrdir, $script.'.js');

        if (file::exists($file))
        {
            return file::read($file);
        }
    }
}

<?php

namespace mysli\frontend; class frontend
{
    const __use = '
        .{ theme }
        mysli.tplp
        mysli.i18n
        mysli.toolkit.{ pkg, response, output, config }
    ';

    static function __init()
    {
        static::find_language();
        return true;
    }

    static function url($uri=null)
    {}

    static function url_by_route($uri=null)
    {}

    static function language()
    {}

    static function render(array $tpls, array $variables=[])
    {
        $theme = theme::get_active();

        if (!pkg::is_enabled($theme))
        {
            return false;
        }

        $template = tplp::select($theme);

        $translator = i18n::select($theme);
        $translator->load($theme);

        $template->set_translator($translator);

        if (!$template->has($tpls[0]))
        {
            if (!isset($tpls[1]) || strpos($tpls[1], '/') === false)
            {
                return false;
            }

            list($pkg, $file) = explode('/', $tpls[1], 2);
            $pkg = pkg::get_path($pkg);
            $template->replace($tpls[0].'.tpl.php', [ $pkg, $file.'.tpl.php' ]);
        }

        output::set($template->render($tpls[0], $variables));
        return true;
    }

    /*
    --- Protected --------------------------------------------------------------
     */

    protected static function find_language()
    {
        $langkey = config::select('mysli.frontend', 'locale.default');
        $locales = config::select('mysli.frontend', 'locale.accept');

        $language = isset($locales[$langkey]) ? $locales[$langkey] : $langkey;

        i18n::select('mysli.frontend')->primary($language);
    }
}

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

        // Set Translator
        $translator = i18n::select($theme);
        $translator->load($theme);
        $template->set_translator($translator);

        // Find template
        foreach ($tpls as $tpl)
        {
            if (is_array($tpl))
            {
                $file = $tpl[0];
                $root = $tpl[1];
            }
            else
            {
                $file = $tpl;
                $root = null;
            }

            if ($template->has($file, $root))
            {
                output::set($template->render($tpl, $variables));
                return true;
            }
        }

        return false;
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

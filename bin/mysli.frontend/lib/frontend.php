<?php

namespace mysli\frontend; class frontend
{
    const __use = '
        .{ theme }
        mysli.tplp
        mysli.i18n
        mysli.toolkit.{ response, output, config }
    ';

    static function __init()
    {
        i18n::select('mysli.frontend');
    }

    /**
     * Render particular template with content, and set output.
     * --
     * @param string $contents
     * @param array  $template
     * @param string $language
     * --
     * @return boolean
     */
    static function render($contents, array $template)
    {}

    /**
     * Output 404!
     * --
     * @return void
     */
    static function error404($route)
    {
        static::set_language($route);

        $theme = theme::get_meta(theme::get_active());

        if (!is_array($theme))
        {
            return false;
        }

        $template = tplp::select($theme['absolute_path']);
        $language = i18n::select('mysli.frontend');
        $language->load($theme['absolute_path'].'/i18n');
        $template->set_translator($language);

        response::set_status(404);

        if ($template->has('error404'))
        {
            output::set($template->render('error404'));
            return true;
        }
        else
        {
            return false;
        }

    }

    /*
    --- Protected --------------------------------------------------------------
     */

    protected static function set_language($route)
    {
        if ($route->option('i18n.language'))
            $langkey = $route->option('i18n.language');
        else
            $langkey = config::select('mysli.frontend', 'locale.default');

        $locales = config::select('mysli.frontend', 'locale.accept');
        $language = isset($locales[$langkey]) ? $locales[$langkey] : $langkey;

        i18n::select('mysli.frontend')->primary($language);
    }
}

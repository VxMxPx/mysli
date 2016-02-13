<?php

namespace mysli\frontend; class frontend
{
    const __use = <<<fin
        .{ theme }
        mysli.tplp
        mysli.i18n
        mysli.toolkit.{ pkg, response, output, config }
        mysli.toolkit.type.{ arr }
fin;

    /**
     * Frontend's variables!
     * --
     * @var array
     */
    protected static $variables = [];

    static function __init()
    {
        $variables = config::select('mysli.frontend')->as_array();

        foreach ($variables as $key => $var)
        {
            if (substr($key, 0, 6) !== 'front.') continue;

            $key = explode('.', $key, 2);
            static::$variables['front'][$key[1]] = $var[1];
        }

        static::find_language();
        return true;
    }

    static function render(array $tpls, array $variables=[])
    {
        $theme = theme::get_active();
        \log::debug("I shall render: `{1}`.", [ __CLASS__, var_export($tpls, true) ]);

        if (!pkg::is_enabled($theme))
        {
            return false;
        }

        $template = tplp::select($theme);

        // Set Translator
        $translator = i18n::select($theme);
        $translator->load($theme);
        $template->set_translator($translator);

        // Set subtitle if there
        if (isset($variables['front']['title']))
        {
            $variables['front']['title'] = str_replace(
                [
                    '{title}',
                    '{subtitle}'
                ],
                [
                    static::$variables['front']['title'],
                    $variables['front']['title']
                ],
                static::$variables['front']['subtitle']
            );
        }

        // Add own variables
        $variables = arr::merge(static::$variables, $variables);

        // Find template
        foreach ($tpls as $tpl)
        {
            if (is_array($tpl))
            {
                $root = tplp::get_path($tpl[0]);
                $file = $tpl[1];
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

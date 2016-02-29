<?php

namespace mysli\frontend; class frontend
{
    const __use = <<<fin
        .{ theme }
        mysli.tplp
        mysli.i18n
        mysli.toolkit.{ pkg, output, config, request }
        mysli.toolkit.type.{ arr }
fin;

    /**
     * Frontend's variables!
     * --
     * @var array
     */
    protected static $variables = [];

    /**
     * Current language.
     * --
     * @var string
     */
    protected static $language = null;

    /**
     * Initialize Frontend.
     * Set default variable from configuration.
     * --
     * @return boolean
     */
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

    /**
     * Render the template and set output.
     * --
     * @param  array  $tpls
     * @param  array  $variables
     * --
     * @return boolean
     */
    static function render(array $tpls, array $variables=[])
    {
        // Get current theme
        $theme = theme::get_active();
        \log::debug(
            "I shall render: `{1}`.",
            [ __CLASS__, var_export($tpls, true) ]);

        // Check if theme is actually enabled...
        if (!pkg::is_enabled($theme))
        {
            return false;
        }

        // Set theme in tplp to be used for rendering
        $template = tplp::select($theme);

        // Load and Set Translator
        $translator = i18n::select([ $theme, 'en', null ]);
        try
        {
            $translator->load($theme);
            $template->set_translator($translator);
        }
        catch (\Exception $e)
        {
            // Pass, simply there's no translations in current theme...
        }

        // Add some defaults
        $variables['front']['language'] = static::get_language();
        $variables['front']['uri'] = request::uri();

        if (!isset($variables['front']['quid']))
            $variables['front']['quid'] = md5(request::url( true, true ));

        // Add own variables
        $variables = arr::merge(static::$variables, $variables);

        // Set subtitle if there
        if (isset($variables['front']['subtitle']))
        {
            $variables['front']['title'] = str_replace(
                ['{title}', '{subtitle}'],
                [$variables['front']['title'], $variables['front']['subtitle']],
                $variables['front']['subtitle_format']
            );
        }

        // Set the type of currently displayed content, e.g. blog-post, ...
        if (!isset($variables['front']['type']))
        {
            $variables['front']['type'] = 'undefined';
        }

        // Find template and render it!
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

    /**
     * Get current language.
     * --
     * @return string
     */
    static function get_language()
    {
        return static::$language;
    }

    /*
    --- Protected --------------------------------------------------------------
     */

    /**
     * Find current language.
     */
    protected static function find_language()
    {
        $langkey = config::select('mysli.frontend', 'locale.default');
        $locales = config::select('mysli.frontend', 'locale.accept');

        static::$language = isset($locales[$langkey])
            ? $locales[$langkey]
            : $langkey;
    }
}

<?php

/**
 * # Locales
 *
 * Please see:
 * https://en.wikipedia.org/wiki/IETF_language_tag
 * http://cldr.unicode.org/
 */
namespace mysli\i18n; class locales
{
    static function get($id=null)
    {
        if ($id)
            return static::has($id) ? static::$common[$id] : null;
        else
            return static::$common;
    }

    static function has($id)
    {
        return isset(static::$common[$id]);
    }

    /*
    --- Protected --------------------------------------------------------------
     */

    /**
     * List of common locales.
     * --
     * @var array
     */
    static $common =
    [
        'sq'    => 'Albanian',
        'ar'    => 'Arabic',
        'be'    => 'Belarusian',
        'bg'    => 'Bulgarian',
        'ca'    => 'Catalan',
        'zh'    => 'Chinese',
        'hr'    => 'Croatian',
        'cs'    => 'Czech',
        'da'    => 'Danish',
        'nl'    => 'Dutch',
        'en-gb' => 'English (United Kingdom)',
        'en-us' => 'English (United States)',
        'en'    => 'English',
        'et'    => 'Estonian',
        'fi'    => 'Finnish',
        'fr'    => 'French',
        'de'    => 'German',
        'el'    => 'Greek',
        'iw'    => 'Hebrew',
        'hi-in' => 'Hindi (India)',
        'hu'    => 'Hungarian',
        'is'    => 'Icelandic',
        'in'    => 'Indonesian',
        'ga'    => 'Irish',
        'it'    => 'Italian',
        'ja'    => 'Japanese',
        'ko'    => 'Korean',
        'lv'    => 'Latvian',
        'lt'    => 'Lithuanian',
        'mk'    => 'Macedonian',
        'ms'    => 'Malay',
        'mt'    => 'Maltese',
        'no-no-ny' => 'Norwegian (Nynorsk)',
        'no'    => 'Norwegian',
        'pl'    => 'Polish',
        'pt'    => 'Portuguese',
        'ro'    => 'Romanian',
        'ru'    => 'Russian',
        'sr'    => 'Serbian',
        'sk'    => 'Slovak',
        'sl'    => 'Slovenian',
        'es'    => 'Spanish',
        'sv'    => 'Swedish',
        'th'    => 'Thai',
        'tr'    => 'Turkish',
        'uk'    => 'Ukrainian',
        'vi'    => 'Vietnamese',
    ];
}

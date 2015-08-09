<?php

namespace mysli\i18n; class parser
{
    const __use = 'mysli.toolkit.type.{ str, arr }';

    /**
     * Parse language file and return to array.
     * --
     * @param string $translation
     * @param string $language    Which language is this file for?
     * --
     * @return array
     */
    public static function process($translation, $language=null)
    {
        $mt = $translation;

        $matches;
        $collection = [
            '.meta' => [
                'language'       => $language,
                'created_on'     => gmdate('YmdHis'),
                'updated_on'     => gmdate('YmdHis'),
                'parser_version' => 1
            ]
        ];

        // Append EOF to the end of string, so that we'll get the last match
        $mt .= "\n# EOF";

        // Standardize line endings
        $mt = str::to_unix_line_endings($mt);

        // Match
        preg_match_all(
            '/(^@[A-Z0-9_]+)(\[[0-9\*\+\-\.a-z,]+\])?[\ \t\n]+(.*?)(?=^@|^#)/sm',
            $mt,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match)
        {
            // Assign key and value
            $key   = trim($match[1], '@');
            $value = trim($match[3]);

            $options = trim($match[2], '[]');

            if ($options === '')
            {
                $options = [];
            }
            else
            {
                $options = str::split_trim($options, ',');
            }

            if (in_array('nl', $options))
            {
                $options = arr::delete_by_value($options, 'nl', false);
            }
            else
            {
                // Eliminate new-lines
                $value = str_replace("\n", ' ', $value);
            }

            if (empty($options))
            {
                $collection[$key]['value'] = $value;
            }
            else
            {
                foreach ($options as $option)
                {
                    if ($option === '')
                    {
                        continue;
                    }

                    $collection[$key][$option]['value'] = $value;
                }
            }
        }

        return $language ? [ $language => $collection ] : $collection;
    }
}

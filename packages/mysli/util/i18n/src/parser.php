<?php

namespace mysli\util\i18n;

__use(__namespace__, '
    mysli.framework.type/str,arr
');

class parser
{
    /**
     * Convert Mysli Translation (mt) to array.
     * @param string $translation
     * @return array
     */
    public static function parse($translation)
    {
        $mt = $translation;

        $matches;
        $collection = [
            '.meta' => [
                'created_on' => gmdate('YmdHis'),
                'modified'   => false
            ]
        ];

        // Append EOF to the end of string, so that we'll get the last match
        $mt .= "\n# EOF";

        // Standardize line endings
        $mt = str::to_unix_line_endings($mt);

        // Match
        preg_match_all(
            '/(^@[A-Z_]+)(\[[0-9\*\+\-\.a-z,]+\])?[\ \t\n]+(.*?)(?=^@|^#)/sm',
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

        return $collection;
    }
}

<?php

/**
 * # Clist
 *
 * Simple categorized (value separated) list, encode/decode.
 * Example of categorized list:
 *
 * HIGH:
 * id value
 * id value
 * MEDIUM:
 * id value
 * LOW:
 * id value
 *
 * Produces:
 *
 * [
 *     'high' => [ [id, value], [id, value] ],
 *     'medium' => [ [id, value] ],
 *     'low' => [ [id, value] ],
 * ]
 */
namespace mysli\toolkit; class clist
{
    /**
     * These are the default encode/decode options.
     * --
     * id           false  Map particular filed (number) to the ID
     * map          null   How fields should be mapped when decoded [ id, id, id ]
     * align        true   Align values on output (insert spaces)
     * unique       true   If `id` set, can one ID share multiple values
     * category_to  false  Map category to {ID} or particular field
     * categories   false  Acceptable categories
     * --
     * @var array
     */
    protected static $default_options = [
        'id'          => false,
        'map'         => null,
        'align'       => true,
        'unique'      => true,
        'category_to' => false,
        'categories'  => false,
    ];

    static function decode($list, $options=[])
    {
        $options = array_merge( static::$default_options, $options );
        $map = is_array($options['map']) ? $options['map'] : false;

        $list = str_replace(["\r\n", "\r"], "\n", $list);
        $list = explode("\n", $list);

        $category = null;
        $items = [];

        foreach ($list as $lineno => $line)
        {
            if (!$line) continue;

            // Grab category
            if (preg_match('#^([A-Z0-9]+)\:$#', $line, $m))
            {
                $category = trim(strtolower($m[1]));
                continue;
            }

            $item = [];
            $line = preg_replace_callback('#(\\\\[ |\\t]+)#', function ($m) {
                return '_QTD('.base64_encode(substr($m[1],1)).')_';
            }, $line);
            $segments = preg_split('#[ \\t]+#', $line, $map ? count($map) : PHP_INT_MAX);

            foreach ($segments as $n => $segment)
            {
                $segment = trim($segment);
                if (strpos($segment, '_QTD'))
                {
                    $segment = preg_replace_callback('#_QTD\((.*?)\)_#', function ($m) {
                        return base64_decode($m[1]);
                    }, $segment);
                }
                $item[($map && isset($map[$n]) ? $map[$n] : (int) $n)] = $segment;
            }

            if ($category
                && (!empty($options['categories'])
                    && in_array($category, $options['categories'])))
            {
                if ($options['category_to'] === '{ID}')
                {
                    $stack = &$items[$category];
                }
                else
                {
                    $item[$options['category_to']] = $category;
                    $stack = &$items;
                }
            }
            else
            {
                $stack = &$items;
            }

            if ($options['id'] !== false)
            {
                $id = trim($item[$options['id']]);
                if ($options['unique'])
                {
                    $stack[$id] = $item;
                }
                else
                {
                    $stack[$id][] = $item;
                }
            }
            else
            {
                $stack[] = $item;
            }

            unset($stack);
        }

        return $items;
    }

    static function decode_file($filename, $options=[])
    {
        return static::decode(file::read($filename), $options);
    }

    static function encode(array $list, $options=[])
    {

    }

    static function encode_file($filename, array $list, $options=[])
    {
        return !!file::write($filename, static::encode($list, $options));
    }
}

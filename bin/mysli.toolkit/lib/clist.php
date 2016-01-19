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
    const __use = '
        .{ exception.clist }
    ';

    /**
     * These are the default encode/decode options.
     * --
     * id           false  Map particular filed (number) to the ID
     * map          null   How fields should be mapped when decoded [ id, id, id ]
     * unique       true   If `id` set, can one ID share multiple values
     * category_to  false  Map category to {ID} or particular field
     * categories   false  Acceptable categories
     * --
     * @var array
     */
    protected static $default_options = [
        'id'          => false,
        'map'         => null,
        'unique'      => true,
        'category_to' => false,
        'categories'  => false,
    ];

    /**
     * Decode clist (string) to array.
     * --
     * @param string $list
     * @param array  $options
     * --
     * @throws mysli\toolkit\exception\clist 10 Unknown category.
     * --
     * @return array
     */
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

                // Valid category?
                if (!in_array($category, $options['categories']))
                {
                    throw new exception\clist(
                        "Unknown category: `{$category}`", 10
                    );
                }

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

    /**
     * Decode a clist file.
     * --
     * @param string $filename
     * @param array  $options
     * --
     * @return array
     */
    static function decode_file($filename, $options=[])
    {
        return static::decode(file::read($filename), $options);
    }

    /**
     * Encode an array to clist.
     * --
     * @param array $list
     * @param array $options
     * --
     * @return string
     */
    static function encode(array $list, $options=[])
    {
        $output = '';
        $options = array_merge(static::$default_options, $options);

        // More than two? Merge it down perhaps?
        if ($options['category_to'] !== '{ID}' && static::array_depth($list) > 2)
        {
            $list = static::array_level_down($list);
        }

        // Filter through categories
        if ($options['categories'])
        {
            foreach ($options['categories'] as $category)
            {
                $output .= strtoupper($category).":\n";

                if ($options['category_to'] === '{ID}')
                {
                    if (isset($list[$category]))
                    {
                        $output .= static::columns($list[$category], $options);
                    }
                }
                else
                {
                    $stack = [];

                    foreach ($list as $item)
                    {
                        if (isset($item[$options['category_to']]) &&
                            $item[$options['category_to']] === $category)
                        {
                            unset($item[$options['category_to']]);
                            $stack[] = $item;
                        }
                    }
                    $output .= static::columns($stack, $options);
                }
            }
        }
        else
        {
            $output = static::columns($list, $options);
        }

        return $output;
    }

    /**
     * Encode an array to clist file.
     * --
     * @param string $filename
     * @param array  $list
     * @param array  $options
     * --
     * @return boolean
     */
    static function encode_file($filename, array $list, $options=[])
    {
        return !!file::write($filename, static::encode($list, $options));
    }

    /**
     * Retrun array columns, for encoding.
     * --
     * @param array $list
     * @param array $options
     * --
     * @return string
     */
    protected static function columns(array $list, array $options)
    {
        $output  = [];
        $longest = [];

        // More than two? Merge it down perhaps?
        if (static::array_depth($list) > 2)
        {
            $list = static::array_level_down($list);
        }

        foreach ($list as $p1 => $items)
        {
            foreach ($items as $p2 => $item)
            {
                // Map?
                if ($options['map'] && !in_array($p2, $options['map']))
                {
                    unset($list[$p1][$p2]);
                    continue;
                }

                // Escape space at this point
                $list[$p1][$p2] = preg_replace('/( +)/m', '\\\\$1', $item);

                if (!isset($longest[$p2]) || $longest[$p2] < strlen($item))
                    $longest[$p2] = strlen($item);
            }
        }

        $output = '';

        foreach ($list as $items)
        {
            if ($options['id'] !== false)
            {
                $oid = $options['id'];
                $output .= str_pad($items[$oid], $longest[$oid]+1);
                unset($items[$oid]);
            }

            foreach ($items as $p => $item)
            {
                $output .= str_pad($item, $longest[$p]+1);
            }

            $output = trim($output);
            $output .= "\n";
        }

        return $output;
    }

    /**
     * Get the depth of an array.
     * --
     * @param array   $array
     * @param integer $max
     * --
     * @return integer
     */
    protected static function array_depth(array $array, $max=20)
    {
        $max_depth = 1;

        foreach ($array as $value)
        {
            if (is_array($value))
            {
                $depth = static::array_depth($value, $max_depth) + 1;

                if ($depth > $max_depth)
                {
                    $max_depth = $depth;
                }
            }
        }

        return $max_depth;
    }

    /**
     * Remove the final level of an array.
     * --
     * @param array $array
     * --
     * @return array
     */
    protected static function array_level_down(array $array)
    {
        $list = [];

        foreach ($array as $items)
        {
            foreach ($items as $item)
            {
                $list[] = $item;
            }
        }

        return $list;
    }
}

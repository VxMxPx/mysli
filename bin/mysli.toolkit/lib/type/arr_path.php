<?php

namespace mysli\toolkit\type; class arr_path
{
    const __use = '.type.{ str, arr }';

    /**
     * Get an array value by path.
     * --
     * @example
     *     array   => ['user' => ['address' => 'My Address']]
     *     path    => user.address
     *     return  => My Address
     * --
     * @param array  $array
     * @param string $path
     * @param mixed  $default
     * --
     * @return mixed
     */
    static function get(array $array, $path, $default=null)
    {
        $path = trim($path, '.');
        $path = str::split($path, '.');
        $get  = $array;

        foreach ($path as $w)
        {
            if (is_array($get) && arr::has($get, $w))
            {
                $get = $get[$w];
            }
            else
            {
                return $default;
            }
        }

        return $get;
    }

    /**
     * Set array value by path.
     * --
     * @example
     *     array  => ['user' => ['address' => 'My Address']]
     *     path   => user.address
     *     value  => 'New Address'
     *     result => ['user' => ['address' => 'New Address']]
     * --
     * @param array  $array
     * @param string $path
     * @param mixed  $value
     * --
     * @return null
     */
    static function set(array &$array, $path, $value)
    {
        $path = trim($path, '.');
        $path = str::split($path, '.');
        $previous = $value;
        $new = [];

        for ($i=count($path); $i--; /*pass*/)
        {
            $segment = $path[$i];
            $new[$segment] = $previous;
            $previous = $new;
            $new = [];
        }

        $array = arr::merge($array, $previous, arr::merge_all);
    }

    /**
     * Remove array value by path.
     * --
     * @example
     *     array  => ['user' => ['address' => 'My Address']]
     *     path   => user/address
     *     result => ['user' => []]
     * --
     * @param array  $array
     * @param string $path
     * --
     * @return null
     */
    static function remove(array &$array, $path)
    {
        $array = static::remove_helper($array, $path, null);
    }

    /**
     * Remove by path helper.
     * --
     * @param array  $array
     * @param string $path
     * @param string $cp
     * --
     * @return array
     */
    protected static function remove_helper(array $array, $path, $cp)
    {
        $result = [];

        foreach ($array as $k => $i)
        {
            $cup = $cp . '.' . $k;

            if (trim($cup, '.') === trim($path,'/'))
            {
                continue;
            }

            if (is_array($i))
            {
                $result[$k] = static::remove_helper($i, $path, $cup);
            }
            else
            {
                $result[$k] = $i;
            }
        }

        return $result;
    }
}

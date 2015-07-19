<?php

/**
 * # Assert
 *
 * Return value of each assertion test is an array with description of asserion.
 * [
 *     'succeed' => boolean,
 *     'expect' => [
 *         'type', 'value', // etc...
 *     ],
 *     'actual' => [
 *         'type', 'value', // etc...
 *     ]
 * ]
 */
namespace mysli\dev\testme; class assert
{
    /**
     * Assert that two values are equal by value and type.
     * --
     * @param  mixed $actual
     * @param  mixed $expect
     * --
     * @return array
     */
    static function equals($actual, $expect)
    {
        $r = [];

        $r['actual'] = self::describe($actual);
        $r['expect'] = self::describe($expect);
        $r['succeed'] = ($actual === $expect);

        return $r;
    }

    /**
     * Assert that value match a pattern.
     * --
     * @param  string $actual
     * @param  string $pattern
     * --
     * @return array
     */
    static function match($actual, $pattern)
    {
        $pattern = preg_quote($pattern);
        $pattern = str_replace('\\*', '.*?', $pattern);
        $pattern = "/{$pattern}/";

        $r = [];

        $r['actual'] = self::describe($actual);
        $r['expect'] = self::describe($pattern);

        $r['succeed'] = preg_match($pattern, $actual);

        return $r;
    }

    /**
     * Assert that value is an instance of.
     * --
     * @param  object $actual
     * @param  string $expect
     * --
     * @return array
     */
    static function instance($actual, $expect)
    {
        $r = [];

        $r['actual'] = self::describe($actual);
        $r['expect'] = [ 'instance', $expect ];

        $r['succeed'] = is_object($actual) && is_a($actual, $expect);

        return $r;
    }


    /**
     * Generate information about variable.
     * @param  mixed $variable
     * --
     * @return array
     */
    static function describe($var)
    {
        $type = strtolower( gettype($var) );

        if ($type === 'object')
        {
            $class = get_class($var);

            if (is_a($var, '\Exception'))
            {
                return [
                    'exception',
                    $class,
                    $var->getCode(),
                    $var->getMessage()
                ];
            }
            else
            {
                return [
                    'instance',
                    $class
                ];
            }
        }
        else
        {
            return [
                $type,
                $var
            ];
        }

    }
}

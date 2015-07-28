<?php

namespace mysli\dev\test; class diff
{
    /**
     * Accept two arrays and generate report, comparing values and types of
     * each item.
     * It will return an array in which each item represent a line:
     * [ boolean $is_diff, integer $level, string $arr1_line, string $arr2_line ]
     * --
     * @param array $expect
     * @param array $result
     * --
     * @return array
     */
    static function generate(array $arr1, array $arr2, $level=1)
    {
        $lines = [];

        foreach ($arr1 as $id => $line)
        {
            if ($level === 1 && is_string($line) && strpos($line, "\n"))
            {
                $arr2[$id] = isset($arr2[$id]) ? explode("\n", $arr2[$id]) : [];
                $line = explode("\n", $line);
                $lines = array_merge(
                    $lines, self::generate($line, $arr2[$id], 1)
                );
                continue;
            }

            $printable_id = $level > 1 ? $id : null;

            // Do not do it if level1 and array, this will provide a bit
            // nice output. It's understandable that level one is
            if (!($level === 1 && is_array($line)))
            {
                // Diff one line
                $lines[] = self::diff_line(
                    $line,
                    (isset($arr2[$id]) ? $arr2[$id] : null),
                    $level,
                    $printable_id,
                    !isset($arr2[$id])
                );
            }

            if (is_array($line))
                $lines = array_merge(
                    $lines,
                    self::generate(
                        $line,
                        (isset($arr2[$id]) ? $arr2[$id] : [null]),
                        $level+1
                    )
                );
        }

        return $lines;
    }

    /**
     * Generate a plain representation of an array. This will generate same
     * output as self::generate() does, but without comparison.
     * Return an array, each item representing a line:
     * [ integer $level, string $line ]
     * --
     * @param array  $arr
     * --
     * @return array
     */
    static function plain(array $arr, $level=1)
    {
        $lines = [];

        foreach ($arr as $id => $line)
        {
            if ($level === 1 && is_string($line) && strpos($line, "\n"))
            {
                $lines = array_merge(
                    $lines, self::plain(explode("\n", $line), 1)
                );
                continue;
            }

            $printable_id = $level > 1 ? $id : null;

            if (!($level === 1 && is_array($line)))
                $lines[] = self::plain_line($line, $level, $printable_id);

            if (is_array($line))
                $lines = array_merge(
                    $lines,
                    self::plain($line, $level+1)
                );
        }

        return $lines;
    }

    /*
    --- Private ----------------------------------------------------------------
     */

    /**
     * Generate a report of a plain line. Return an array.
     * This is consistent with diff line output.
     * --
     * @param mixed   $line
     * @param integer $level
     * @param string  $id
     * --
     * @return array
     *         [
     *             boolean (false) $is_diff,
     *             integer $level,
     *             string $arr1_line,
     *             string (null) $arr2_line
     *         ]
     */
    private static function plain_line($line, $level, $id=null)
    {
        $line = self::stringify($line);

        if ($id !== null)
            $line = "{$id}: {$line}";

        return [ false, $level, $line, null ];
    }

    /**
     * Generate report of a line. Return an array.
     * --
     * @param mixed   $line1
     * @param mixed   $line2
     * @param integer $level
     * @param string  $id
     * @param boolean $was_miss
     * --
     * @return array
     *         [
     *             boolean $is_diff,
     *             integer $level,
     *             string  $arr1_line,
     *             string  $arr2_line
     *         ]
     */
    private static function diff_line(
        $line1, $line2, $level, $id=null, $was_miss=false)
    {
        // Get line1 type
        $l1_type = gettype($line1);
        if ($l1_type === 'object')
            $l1_type = get_class($line1);


        // Miss? Quit...
        if ($was_miss)
        {
            $line1 = self::stringify($line1);
            $line1 = "[{$l1_type}] {$line1}";
            $line1 = $id !== null ? "{$id}: {$line1}" : $line1;
            return [ true, $level, $line1, null ];
        }

        // Get line2 type
        $l2_type = gettype($line2);
        if ($l2_type === 'object')
            $l2_type = get_class($line2);

        // They're the same...
        if ($line1 === $line2)
        {
            $line1 = self::stringify($line1);
            $line1 = $id !== null ? "{$id}: {$line1}" : $line1;
            $line2 = self::stringify($line2);
            $line2 = $id !== null ? "{$id}: {$line2}" : $line2;
            return [ false, $level, $line1, $line2 ];
        }

        $line1 = self::stringify($line1);
        $line2 = self::stringify($line2);

        // Type diff?
        if ($l1_type !== $l2_type)
        {
            $line1 = "[{$l1_type}] {$line1}";
            $line2 = "[{$l2_type}] {$line2}";
        }

        // Append id if there...
        $line1 = $id !== null ? "{$id}: {$line1}" : $line1;
        $line2 = $id !== null ? "{$id}: {$line2}" : $line2;

        return [ true, $level, $line1, $line2 ];
    }

    /**
     * Convert a non-printable type to string.
     * --
     * @param mixed $value
     * --
     * @return string
     */
    private static function stringify($value)
    {
        if (is_array($value))
        {
            return empty($value) ? '[array][empty]' : '[array]';
        }
        elseif (is_object($value))
        {
            return '[object]';
        }
        elseif (is_resource($value))
        {
            return '[resource]';
        }
        elseif (is_string($value))
        {
            // Strip shell arguments if there...
            if (preg_match('/\\e\[[0-9]+m/', $value))
                $value = escapeshellcmd($value);
            else
                $value = $value;

            return '"'.$value.'"';
        }
        elseif (is_bool($value))
        {
            return $value ? 'True' : 'False';
        }
        else
        {
            return $value;
        }
    }
}

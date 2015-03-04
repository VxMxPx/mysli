<?php

namespace mysli\dev\phpt;

__use(__namespace__, '
    mysli.framework.fs/fs,file
    mysli.framework.exception/*  AS  framework\exception\*
');

class generator
{
    /**
     * Read a php file and return list of methods in a class
     * @param  string  $file
     * @return array
     */
    static function get_methods($file)
    {
        $methods = [];

        if (!file::exists($file))
            throw new framework\exception\not_found(
                "File not found: {$file}", 1
            );

        $contents = file::read($file);

        // get namespace
        preg_match(
            '/^[ \\t]*?namespace ([a-z0-9_\\\\]+) ?(\\{?|;?)$/im',
            $contents, $namespace
        );

        $namespace = isset($namespace[1]) ? $namespace[1] : false;

        if (!$namespace)
            throw new framework\exception\data(
                "Missing namespace: `{$file}`", 1
            );

        // get class
        preg_match(
            '/^[ \\t]*?class ([a-z0-9_]+) ?/im', $contents, $class
        );

        $class = isset($class[1]) ? $class[1] : false;

        if (!$class)
            throw new framework\exception\data(
                "Missing class: `{$file}`", 2
            );

        // Get functions
        preg_match_all(
            '/^[ \\t]+([a-z]+) ?([a-z0-9_]+)? ?([a-z0-9_]+)? ([a-z0-9_]+) *?\\(/im',
            $contents,
            $methods_raw,
            PREG_SET_ORDER
        );

        foreach ($methods_raw as $method)
        {
            if (!in_array('function', $method))
                continue;

            $method = explode('function ', $method[0], 2);
            $meta   = trim($method[0]);
            $name   = substr(trim($method[1]), 0, -1);
            $visibility = 'public';
            $static     = false;

            if (!empty($meta))
            {
                $meta = explode(' ', $meta, 2);

                if (trim($meta[0]) === 'static')
                {
                    $static = true;
                    $meta = isset($meta[1]) ? $meta[1] : false;
                }
                elseif (isset($meta[1]) && trim($meta[1]) === 'static')
                {
                    $static = true;
                    $meta = $meta[0];
                } else
                    $meta = $meta[0];

                if (in_array(trim($meta), ['public', 'protected', 'private']))
                    $visibility = trim($meta);
            }

            $methods[$name] = [
                'visibility' => $visibility,
                'static'     => $static
            ];
        }

        // Get docblocks
        preg_match_all(
            '/^[ \\t]+\\/\\*\\*\\s(.+?)\\*\\/\\s[ a-z\\t]+(function [a-z0-9_]+ *?\\()/ism',
            $contents,
            $docblocks_raw,
            PREG_SET_ORDER
        );

        foreach ($docblocks_raw as $docblock)
        {
            $function = isset($docblock[2])
                ? substr(trim($docblock[2]), 0, -1)
                : false;

            if (!$function)
                continue;
            else
                $function = explode(' ', $function, 2)[1];

            if (isset($methods[$function]))
                $methods[$function]['description'] = self::get_doc_description($docblock[1]);

        }

        return [
            'namespace' => $namespace,
            'class'     => $class,
            'methods'   => $methods
        ];
    }
    /**
     * Generate test string
     * @param  array $options keys: test, description, skipif, file, expect
     * @return string
     */
    static function make(array $options=[])
    {
        $output = '';

        // --TEST--
        $output .= "--TEST--\n";
        if (isset($options['test']))
            $output .= "{$options['test']}\n";

        // --DESCRIPTION--
        if (isset($options['description']))
            $output .= "--DESCRIPTION--\n{$options['description']}\n";

        // --SKIPIF--
        if (isset($options['skipif']))
            $output .= "--SKIPIF--\n{$options['skipif']}\n";

        // --FILE--
        $output .= "--FILE--\n";
        if (isset($options['file']))
            $output .= "{$options['file']}\n";

        // --EXPECT--
        $output .= "--EXPECT--\n";
        if (isset($options['expect']))
            $output .= "{$options['expect']}\n";

        // Output
        return $output;
    }

    /**
     * Extract descriptionsection from docblock.
     * @param  string $docblock
     * @return string
     */
    private static function get_doc_description($docblock)
    {
        $docblock = explode('* @', $docblock, 2);
        $docblock = explode("\n", $docblock[0]);
        $docs = '';

        foreach ($docblock as $docblock_line)
            $docs .= trim($docblock_line, " \\t*");

        return $docs;
    }
}

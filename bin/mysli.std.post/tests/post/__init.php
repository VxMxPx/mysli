<?php

namespace mysli\std\post\root\tests\post;

\autoloader::override(
    [
        'mysli.toolkit.fs.file' => 'mysli.std.post.root.tests.post.mock',
        'mysli.toolkit.fs.dir'  => 'mysli.std.post.root.tests.post.mock',
        'mysli.toolkit.json'    => 'mysli.std.post.root.tests.post.mock',

    ],
    'mysli\std\post');

class post extends \mysli\std\post\post
{
    function __t_set_meta(array $m)
    {
        $this->meta = $m;
    }

    function __t_set_html($html)
    {
        $this->html = $html;
    }

    function __t_set_source($meta, $source)
    {
        $this->source['body'] = $source;
        $this->source['meta'] = $meta;
    }

    function get_hash($fresh)
    {
        return '4b5cc523a5bd61848fa2f193b574b889';
    }

    function load_source()
    {
        $this->source['meta'] = [];
        $this->source['body'] = '';
    }
}

class mock
{
    protected static $__t_actions = [];

    static function __t_get_actions()
    {
        return static::$__t_actions;
    }

    static function __callStatic($name, $arguments)
    {
        static::$__t_actions[$name][] = $arguments;
        return true;
    }

    static function read($filename)
    {
        return '';
    }

    static function exists($filename)
    {
        return !(strpos($filename, 'cache~'));
    }

    static function decode_file($filename)
    {
        return [];
    }
}

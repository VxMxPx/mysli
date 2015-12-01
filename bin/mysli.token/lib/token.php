<?php

namespace mysli\token; class token
{
    const __use = '
        mysli.toolkit.fs.{ fs, dir, file }
        mysli.toolkit.{ json, type.int -> int, request }
    ';

    /**
     * Data source file-path.
     * --
     * @var string
     */
    protected static $data_source;

    /**
     * Tokens registry.
     * --
     * @var array
     */
    protected static $registry;

    /**
     * Initialize token.
     */
    static function __init()
    {
        static::set_data_source(fs::cntpath('tokens/r.json'));
        static::reload();
        return true;
    }

    /**
     * Set registry path.
     * --
     * @param string $path
     */
    static function set_data_source($filename)
    {
        if (!file::exists($filename))
        {
            file::write(fs::cntpath('tokens/r.json'), '[]');
        }

        static::$data_source = $filename;
    }

    /**
     * Reload tokens list.
     */
    static function reload()
    {
        static::$registry = json::decode_file(static::$data_source, true);

        if (!isset(static::$registry['qid']))
        {
            static::$registry['qid'] = static::generate_qid();
        }

        if (!isset(static::$registry['tokens']))
        {
            static::$registry['tokens'] = [];
        }
    }

    /**
     * Remove token by id.
     * --
     * @param string $id
     * --
     * @return boolean
     */
    static function remove($id)
    {
        if (isset(static::$registry['tokens'][$id]))
        {
            unset(static::$registry['tokens'][$id]);
            return static::write();
        }
        else
        {
            return false;
        }
    }

    /**
     * Get data by token's id.
     * --
     * @param string $id
     * --
     * @return string null if not found.
     */
    static function get($id)
    {
        if (isset(static::$registry['tokens'][$id]))
        {
            $token = static::$registry['tokens'][$id];

            if (time() > $token['expires_on'])
            {
                static::remove($id);
                return false;
            }

            return $token['data'];
        }
        else
        {
            return false;
        }
    }

    /**
     * Create token for particular user.
     * --
     * @param string  $data
     * @param integer $expires in seconds
     * --
     * @return string
     */
    static function create($data, $expires=1440)
    {
        $id = static::generate_qid(static::$registry['qid']);
        $id = "t1{$id}";
        $token = [
            'data'        => $data,
            'created_on'  => time(),
            'expires_on'  => time() + $expires,
            'expires_set' => $expires,
            'ip'          => request::ip()
        ];
        static::$registry['tokens'][$id] = $token;
        static::write();
        return $id;
    }

    /**
     * Remove expired tokens.
     * --
     * @return integer number of removed tokens.
     */
    static function cleanup()
    {
        $now  = time();
        $rnum = 0;

        foreach (static::$registry['tokens'] as $tid => $tdat)
        {
            if ($now > $tdat['expires_on'])
            {
                static::$registry['tokens'][$tid];
                $rnum++;
            }
        }

        if ($rnum > 0)
        {
            static::write();
        }

        return $rnum;
    }

    /**
     * Generate unique ID.
     * --
     * @param $salt string
     * --
     * @return string
     */
    private static function generate_qid($salt=null)
    {
        return sha1(
            sha1(md5(time())) .
            int::random(10000, 99999) .
            sha1(md5($salt))
        );
    }

    /**
     * Write changes to file.
     * --
     * @return boolean
     */
    private static function write()
    {
        static::$registry['qid'] = static::generate_qid(static::$registry['qid']);
        return json::encode_file(static::$data_source, static::$registry);
    }
}

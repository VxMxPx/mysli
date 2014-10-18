<?php

namespace mysli\web\token;

__use(__namespace__,
    'mysli/framework/fs',
    'mysli/framework/json',
    'mysli/framework/type/int'
);

class token {

    private static $data_source;
    private static $registry;

    /**
     * Set registry path.
     * @param string $path
     */
    static function set_data_source($path) {
        self::$data_source = $path;
    }
    /**
     * Reload tokens list
     */
    static function reload() {
        self::$registry = json::decode_file(self::$data_source, true);
        if (!isset(self::$registry['qid'])) {
            self::$registry['qid'] = self::generate_qid();
        }
        if (!isset(self::$registry['tokens'])) {
            self::$registry['tokens'] = [];
        }
    }
    /**
     * Remove token by id.
     * @param  string $id
     * @return boolean
     */
    static function remove($id) {
        if (isset(self::$registry['tokens'][$id])) {
            unset(self::$registry['tokens'][$id]);
            return self::write();
        } else {
            return false;
        }
    }
    /**
     * Get data by token's id.
     * @param  string $id
     * @return string false if not found.
     */
    static function get($id) {
        if (isset(self::$registry['tokens'][$id])) {
            $token = self::$registry['tokens'][$id];
            if (time() > $token['expires_on']) {
                self::remove($id);
                return false;
            }
            return $token['data'];
        } else {
            return false;
        }
    }
    /**
     * Create token for particular user
     * @param  string  $data
     * @param  integer $expires in seconds
     * @return string
     */
    static function create($data, $expires=1440) {
        $id = self::generate_qid(self::$registry['qid']);
        $id = "t1{$id}";
        $token = [
            'data'        => $data,
            'created_on'  => time(),
            'expires_on'  => time() + $expires,
            'expires_set' => $expires,
            'ip'          => request::ip()
        ];
        self::$registry['tokens'][$id] = $token;
        self::write();
        return $id;
    }
    /**
     * Remove expired tokens.
     * @return integer  number of removed tokens
     */
    static function cleanup() {
        $now  = time();
        $rnum = 0;

        foreach (self::$registry['tokens'] as $tid => $tdat) {
            if ($now > $tdat['expires_on']) {
                self::$registry['tokens'][$tid];
                $rnum++;
            }
        }

        if ($rnum > 0) {
            self::write();
        }
        return $rnum;
    }

    /**
     * Generate unique ID.
     * @param  $salt string
     * @return string
     */
    private static function generate_qid($salt=null) {
        return
            sha1(
                sha1(md5(time())) .
                int::random(10000, 99999) .
                sha1(md5($salt)));
    }
    /**
     * Write changes to file.
     * @return boolean
     */
    private static function write() {
        self::$registry['qid'] = self::generate_qid(self::$registry['qid']);
        return json::encode_file(self::$data_source, self::$registry);
    }
}

<?php

namespace Mysli\Token;

class Token
{
    // Collection of all tokens
    private $registry = [];

    public function __construct()
    {
        $this->registry = \Core\JSON::decode_file(datpath('mysli.token/registry.json'), true);
        $this->registry = is_array($this->registry)
            ? $this->registry
            : [
                'qid'    => $this->generate_qid(),
                'tokens' => []
            ];
    }

    /**
     * Write changes to file.
     * --
     * @return boolean
     */
    private function write()
    {
        $this->registry['qid'] = $this->generate_qid($this->registry['qid']);
        return \Core\JSON::encode_file(datpath('mysli.token/registry.json'), $this->registry);
    }

    /**
     * Generate unique ID.
     * --
     * @param $salt string
     * --
     * @return string
     */
    private function generate_qid($salt = null)
    {
        return sha1( md5( time() ) . md5( mt_rand() ) . md5( $salt ) );
    }

    /**
     * Remove token by id.
     * --
     * @param  string $id
     * --
     * @return boolean
     */
    public function remove($id)
    {
        if (isset($this->registry['tokens'][$id])) {
            unset($this->registry['tokens'][$id]);
            return $this->write();
        } else return false;
    }

    /**
     * Get data by token's id.
     * --
     * @param  string $id
     * --
     * @return string False if not found.
     */
    public function get($id)
    {
        if (isset($this->registry['tokens'][$id])) {
            $token = $this->registry['tokens'][$id];
            if (time() > $token['expires_on']) {
                $this->remove($id);
                return false;
            }
            return $token['data'];
        } else return false;
    }

    /**
     * Create token for particular user
     * --
     * @param  string $data
     * --
     * @return string
     */
    public function create($data, $expires = 1440)
    {
        $id = $this->generate_qid($this->registry['qid']);
        $id = "t1{$id}";
        $token = [
            'data'        => $data,
            'created_on'  => time(),
            'expires_on'  => time() + $expires,
            'expires_set' => $expires,
            'ip'          => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 0
        ];
        $this->registry['tokens'][$id] = $token;
        $this->write();
        return $id;
    }

    /**
     * Remove expired tokens.
     * --
     * @return integer  Number of removed tokens
     */
    public function cleanup()
    {
        $now  = time();
        $rnum = 0;

        foreach ($this->registry['tokens'] as $tid => $tdat) {
            if ($now > $tdat['expires_on']) {
                $this->registry['tokens'][$tid];
                $rnum++;
            }
        }

        if ($rnum > 0) $this->write();
        return $rnum;
    }
}

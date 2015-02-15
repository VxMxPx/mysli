<?php

namespace mysli\util\curl;

__use(__namespace__, '
    mysli.framework.fs/fs,dir
    mysli.util.config
');

class curl {

    private $resource;
    private $options = [];

    /**
     * Make a get request
     * @param  mixed $curl string|mysli\web\curl\curl
     * @return mixed string(result)|mysli\web\curl\curl
     */
    static function get($curl) {

        $instance = $curl instanceof self;

        if (!$instance) {
            $curl = new self($curl);
        }

        $default = config::select('mysli/util/curl', 'default');
        $options = [
            CURLOPT_USERAGENT      => self::get_user_agent(),
            CURLOPT_HEADER         => false,
            CURLOPT_RETURNTRANSFER => true,
        ];
        if ($default) {
            $options = self::options_merge($default, $options);
        }
        $curl->set_opt($options, null, false);

        return $instance ? $curl : $curl->exec();
    }
    /**
     * Make a post request
     * @param  mixed $curl string|mysli\web\curl\curl
     * @param  array $data
     * @return mixed string(result)|mysli\web\curl\curl
     */
    static function post($curl, array $data=[]) {

        $instance = $curl instanceof self;

        if (!$instance) {
            $curl = new self($curl);
        }

        $default = config::select('mysli/util/curl', 'default');
        $options = [
            CURLOPT_USERAGENT      => self::get_user_agent(),
            CURLOPT_POST           => true,
            CURLOPT_HEADER         => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS     => http_build_query($data)
        ];
        if ($default) {
            $options = self::options_merge($default, $options);
        }
        $curl->set_opt($options, null, false);

        return $instance ? $curl : $curl->exec();
    }
    /**
     * With cookie can be used to set cookie on existing object,
     * or as: curl::get(curl::with_cookie('http://url...'));
     * @param  mixed $curl
     * @return mysli\web\curl\curl
     */
    static function with_cookie($curl) {

        $instance = $curl instanceof self;

        if (!$instance) {
            $curl = new self($curl);
        }

        $curl->set_opt([
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_COOKIEJAR      => self::get_cookie_path(),
            CURLOPT_COOKIEFILE     => self::get_cookie_path()
        ]);

        return $curl;
    }
    /**
     * Get full absolute path to the cookie file.
     * If file not provided,default will be used.
     * @param  string $file
     * @return string
     */
    static function get_cookie_path($file=null) {
        if (!$file) {
            $file = config::select('mysli/util/curl', 'cookie_filename');
        }
        return fs::datpath('mysli/util/curl/', $file);
    }
    /**
     * Get user agent as specified in config.
     * @return string
     */
    static function get_user_agent() {

        $c = config::select('mysli/util/curl');

        if (isset($_SERVER['HTTP_USER_AGENT']) && $c->get('user_agent')) {
            return $_SERVER['HTTP_USER_AGENT'];
        } else {
            return $c->get('costume_agent');
        }
    }
    /**
     * Do a proper merge of two arrays (e.g. do not mess with keys!!)
     * @param  array  $arr1
     * @param  array  $arr2
     * @return array
     */
    private static function options_merge(array $arr1, array $arr2) {
        foreach ($arr2 as $k => $v) {
            if (!array_key_exists($k, $arr1)) {
                $arr1[$k] = $v;
            }
        }
        return $arr1;
    }

    /**
     * Instance of Curl
     * @param string $url
     */
    function __construct($url=null) {
        $this->resource = curl_init($url);
    }
    /**
     * Close curl
     */
    function __destruct() {
        curl_close($this->resource);
    }
    /**
     * Perform a cURL session
     * @return mixed false on failure
     */
    function exec() {
        $result = curl_exec($this->resource);
        if (!$result) {
            throw new exception\curl(
                'Error no.: `' . curl_errno($this->resource) . '`: '.
                curl_error($this->resource));
        }
        return $result;
    }
    /**
     * Get information regarding a specific transfer.
     * @param  integer $opt
     * @return mixed
     */
    function get_info($opt=0) {
        return curl_getinfo($this->resource, $opt);
    }
    /**
     * Get curl resource
     * @return resource
     */
    function get_resource() {
        return $this->resource;
    }
    /**
     * Set option.
     * @param mixed   $opt   string|array
     * @param mixed   $value if $opt is string, then value for it.
     * @param boolean $overwrite if option is set, should it be overwritten
     */
    function set_opt($opt, $value=false, $overwrite=true) {
        if (!is_array($opt)) {
            $opt = [$opt => $value];
        }
        foreach ($opt as $k => $val) {
            if (!$overwrite && array_key_exists($k, $this->options)) {
                continue;
            }
            $this->options[$k] = $val;
            if (!@curl_setopt($this->resource, $k, $val)) {
                throw new exception\curl("Invalid cURL option: `{$k}`.");
            }
        }
    }
}

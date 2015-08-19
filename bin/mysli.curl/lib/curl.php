<?php

namespace mysli\curl; class curl
{
    const __use = '
        mysli.toolkit.{
            fs.fs  -> fs,
            fs.dir -> dir,
            config
        }
        .exception.curl
    ';

    /**
     * Resource of current instance.
     * --
     * @var resource
     */
    private $resource;

    /**
     * Currently set options, with which the request(s) will be made.
     * --
     * @var array
     */
    private $options = [];

    /**
     * Instance of Curl.
     * --
     * @param string $url
     */
    function __construct($url=null)
    {
        $this->resource = curl_init($url);
    }

    /**
     * Close curl.
     */
    function __destruct()
    {
        curl_close($this->resource);
    }

    /**
     * Execute a cURL session.
     * --
     * @throws mysli\curl\exception\curl 10 Curl error.
     * --
     * @return mixed false on failure
     */
    function exec()
    {
        $result = curl_exec($this->resource);

        if (!$result)
            throw new exception\curl(
                'Error no.: `' . curl_errno($this->resource) . '`: '.
                curl_error($this->resource), 10
            );

        log::info("Executed: `".curl_getinfo($this->resource)."`.", __CLASS__);

        return $result;
    }

    /**
     * Get information regarding a specific transfer.
     * --
     * @param integer $opt
     * --
     * @return mixed
     */
    function get_info($opt=0)
    {
        return curl_getinfo($this->resource, $opt);
    }

    /**
     * Get curl resource.
     * --
     * @return resource
     */
    function get_resource()
    {
        return $this->resource;
    }

    /**
     * Set option.
     * --
     * @throws mysli\curl\exception\curl 10 Invalud cURL option.
     * --
     * @param mixed   $opt       string|array
     * @param mixed   $value     If $opt is string, then value for it.
     * @param boolean $overwrite If option is set, should it be overwritten?
     */
    function set_opt($opt, $value=false, $overwrite=true)
    {
        if (!is_array($opt))
            $opt = [$opt => $value];

        foreach ($opt as $k => $val)
        {
            if (!$overwrite && array_key_exists($k, $this->options))
                continue;

            $this->options[$k] = $val;

            if (!@curl_setopt($this->resource, $k, $val))
                throw new exception\curl("Invalid cURL option: `{$k}`.", 10);
        }
    }

    /*
    --- Static -----------------------------------------------------------------
     */

    /**
     * Make a get request.
     * --
     * @param string  $url
     * @param boolean $cookie
     *        Make a request with cookies.
     * --
     * @return string
     */
    static function get($url, $cookie=false)
    {
        $curl = new self($url);
        static::set_defaults($curl);
        static::set_get($curl);
        if ($cookie)
            static::set_cookie($curl);

        /*
        Get result, destroy instance and return.
         */
        $r = $curl->exec();
        unset($curl);
        return $r;
    }

    /**
     * Make a post request.
     * --
     * @param string  $curl
     * @param array   $data
     * @param boolean $cookie
     *        Make a request with cookies.
     * --
     * @return string
     */
    static function post($url, array $data=[], $cookie=false)
    {
        $curl = new self($url);
        static::set_defaults($curl);
        static::set_post($curl, $data);
        if ($cookie)
            static::set_cookie($curl);

        /*
        Get result, destroy instance and return.
         */
        $r = $curl->exec();
        unset($curl);
        return $r;
    }

    /**
     * Set cookie options on existing object.
     * --
     * @param \mysli\curl\curl $curl
     */
    static function set_cookie(\mysli\curl\curl $curl)
    {
        $curl->set_opt([
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_COOKIEJAR      => static::get_cookie_path(),
            CURLOPT_COOKIEFILE     => static::get_cookie_path()
        ]);
    }

    /**
     * Set post options on existing cURL object.
     * --
     * @param \mysli\curl\curl $curl
     * @param array $data
     */
    static function set_post(\mysli\curl\curl $curl, array $data)
    {
        $curl->set_opt([
            CURLOPT_USERAGENT      => static::get_user_agent(),
            CURLOPT_POST           => true,
            CURLOPT_HEADER         => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS     => http_build_query($data)
        ]);
    }

    /**
     * Set get options on existing cURL object.
     * --
     * @param \mysli\curl\curl $curl
     * @param array $data
     */
    static function set_get(\mysli\curl\curl $curl)
    {
        $curl->set_opt([
            CURLOPT_USERAGENT      => static::get_user_agent(),
            CURLOPT_HEADER         => false,
            CURLOPT_RETURNTRANSFER => true,
        ]);
    }

    /**
     * Set default options on a existing curl object.
     * --
     * @param \mysli\curl\curl $curl
     */
    static function set_defaults(\mysli\curl\curl $curl)
    {
        $curl->set_opt(config::select('mysli.curl', 'default', []));
    }

    /*
    --- Private ----------------------------------------------------------------
     */

    /**
     * Get full absolute path to the cookie file.
     * If file not provided,default will be used.
     * --
     * @param string $file
     * --
     * @return string
     */
    static function get_cookie_path($file=null)
    {
        if (!$file)
            $file = config::select('mysli.curl', 'cookie_filename');

        return fs::tmppath('mysli.curl', $file);
    }

    /**
     * Get user agent as specified in config.
     * --
     * @return string
     */
    static function get_user_agent()
    {
        $c = config::select('mysli.curl');

        if (isset($_SERVER['HTTP_USER_AGENT']) && $c->get('agent_fetch'))
            return $_SERVER['HTTP_USER_AGENT'];
        else
            return $c->get('agent_costume');
    }

    /**
     * Do a proper merge of two arrays (e.g. do not mess with keys!!)
     * --
     * @param array $arr1
     * @param array $arr2
     * --
     * @return array
     */
    private static function options_merge(array $arr1, array $arr2)
    {
        foreach ($arr2 as $k => $v)
        {
            if (!array_key_exists($k, $arr1))
                $arr1[$k] = $v;
        }

        return $arr1;
    }
}

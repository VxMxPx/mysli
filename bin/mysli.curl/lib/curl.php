<?php

/**
 * # CURL
 *
 * A simple cURL interface.
 *
 * ##
 *
 * To make a simple request, static `get` or `post` methods can be used:
 *
 *      curl::get('http://domain.tld/page');
 *      curl::post('http://domain.tld/', ['key' => 'value']);
 *
 * If you wish to make request with cookies, set `$cookie` to true:
 *
 *      curl::get('http://domain.tld', true);
 *
 * For advanced options, self can be instantiated:
 *
 *      $curl = new curl($url);
 *      $curl->set_opt(CURLOPT_FOLLOWLOCATION, false);
 *      $curl->exec();
 *
 * You can apply default, cookie, get or post options on constructed object:
 *
 *      $curl = new curl($url);
 *      self::set_defaults($curl);
 *      $curl->set_opt(CURLOPT_FOLLOWLOCATION, false);
 *      self::set_cookie($curl);
 *      $curl->exec();
 *
 * ## Configuration
 *
 * **DEFAULT**\\
 * Default initial options used by each get / post request.
 *
 *     array mysli.curl, default [
 *         CURLOPT_FOLLOWLOCATION => true,
 *         CURLOPT_ENCODING       => '',
 *         CURLOPT_AUTOREFERER    => true,
 *         CURLOPT_CONNECTTIMEOUT => 8,
 *         CURLOPT_TIMEOUT        => 8,
 *         CURLOPT_MAXREDIRS      => 8
 *     ]
 *
 * **USER_AGENT**\\
 * Weather to acquire and use user agent from current user.
 *
 *      boolean mysli.curl, agent_fetch, [true]
 *
 * **COSTUME_AGENT**\\
 * A fallaback, if user's agent couldn't be fetched,
 * or agent_fetch is set to false.
 *
 *      string mysli.curl, agent_costume, [
 *          Mozilla/5.0 (X11; Linux x86_64; rv:35.0) Gecko/20100101 Firefox/35.0
 *      ]
 *
 * **COOKIE_FILENAME**\\
 * Cookie will be saved in {tmppath}/mysli.curl/{cookie_filename}
 *
 *      string mysli.curl, cookie_filename [cookies.txt]
 */
namespace mysli\curl; class curl
{
    const __use = '
        mysli.toolkit.{
            fs,
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
        self::set_defaults($curl);
        self::set_get($curl);
        if ($cookie)
            self::set_cookie($curl);

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
        self::set_defaults($curl);
        self::set_post($curl, $data);
        if ($cookie)
            self::set_cookie($curl);

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
            CURLOPT_COOKIEJAR      => self::get_cookie_path(),
            CURLOPT_COOKIEFILE     => self::get_cookie_path()
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
            CURLOPT_USERAGENT      => self::get_user_agent(),
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
            CURLOPT_USERAGENT      => self::get_user_agent(),
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

<?php

namespace Mysli\Web;

class Web
{
    use \Mysli\Core\Pkg\Singleton;

    protected $pubpath;

    protected $event;
    protected $request;
    protected $response;
    protected $output;
    protected $config;

    /**
     * Construct Web object.
     * --
     * @param object $config   ~config
     * @param object $event    ~event
     * @param object $request  ~request
     * @param object $response ~response
     * @param object $output   ~output
     */
    public function __construct(
        \Mysli\Config\Config $config,
        \Mysli\Event\Event $event,
        \Mysli\Request\Request $request,
        \Mysli\Response\Response $response,
        \Mysli\Output\Output $output
    ) {
        // This is defined in index.php
        // $this->pubpath = MYSLI_PUBPATH;
        $this->pubpath = realpath(datpath($config->get('relative_path')));

        $this->config = $config;
        $this->event = $event;
        $this->request = $request;
        $this->response = $response;
        $this->output = $output;
    }

    /**
     * Routing!
     * --
     * @return null
     */
    public function route()
    {
        // Get route and remove any * < > character.
        $route = implode('/', $this->request->segments());
        $route = str_replace(['*', '<', '>'], '', $route);
        // Get method (post,delete,put,get)
        $method = strtolower($this->request->get_method());

        // Events...
        $this->event->trigger(
            "mysli/web/route:{$method}<{$route}>",
            [$this->response, $method, $route]
        );

        if ($this->response->get_status() === 0) {
            $this->response->status_404_not_found();
        }

        if ($this->response->get_status() === 404) {
            $this->event->trigger('mysli/web/route:404');
        }
    }

    /**
     * Apply headers and set output.
     * --
     * @return null
     */
    public function output(&$output)
    {
        if ($this->response->get_status() === 0) {
            $this->response->status_200_ok();
        }
        $this->response->apply_headers();

        $output  = is_string($output) ? $output : '';
        $output .= $this->output->as_html();
    }

    /**
     * Get absolute public path.
     * --
     * @param  string ... Append to the path.
     * --
     * @return string
     */
    public function path()
    {
        $arguments = func_get_args();
        $arguments = implode(DIRECTORY_SEPARATOR, $arguments);
        return ds($this->pubpath, $arguments);
    }

    /**
     * Get base URL, with appended URI (if so desired).
     * --
     * @param  string $uri
     * --
     * @return string
     */
    public function url($uri = null)
    {
        $url = $this->config->get('url', $this->get_current_url());
        $url = rtrim($url, '/') . '/'; // Always ending slash!
        if ($uri) {
            $url .= ltrim($uri, '/');
        }
        return $url;
    }

    /**
     * Get base URL, modify GET
     * --
     * @param  string $key
     * @param  string $val
     * @param  string $uri
     * --
     * @return string
     */
    public function url_query($key, $val, $uri = null) {
        $query = $_GET;
        $query[$key] = $val;
        return $this->url($uri) . '?' . http_build_query($query);
    }

    /**
     * Will get current domain
     * --
     * @return string
     */
    public function get_domain()
    {
        return $_SERVER['SERVER_NAME'];
    }

    /**
     * Return current url, if with_query is set to true, it will return full url,
     * query included.
     * --
     * @param  boolean $with_query
     * @param  boolean $trim       Will remove end slash
     * --
     * @return  string
     */
    public function get_current_url($with_query = false, $trim = true)
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            $url = ($this->is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
        } else {
            $url = '';
        }

        if ($with_query) {
            // Make sure we have ending '/'!
            $url = trim($url, '/') . '/';
            $url = $url . ltrim($_SERVER['REQUEST_URI'], '/');
        }

        if ($trim) {
            $url = rtrim($url, '/');
        }

        return $url;
    }

    /**
     * Check if we're on SSL connection.
     * _Borrowed from Wordpress_
     * --
     * @return boolean
     */
    public function is_ssl() {
        if (isset($_SERVER['HTTPS'])) {
            if ('on' == strtolower($_SERVER['HTTPS'])) {
                return true;
            }
            if ('1' == $_SERVER['HTTPS']) {
                return true;
            }
        } elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
            return true;
        }
        return false;
    }
}

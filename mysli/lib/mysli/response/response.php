<?php

namespace Mysli;

class Response
{
    protected $headers = [];

    protected $event;

    /**
     * Construct RESPONSE
     * --
     * @param object $event ~event
     */
    public function __construct($event)
    {
        $this->event = $event;
    }

    /**
     * Will apply headers.
     * --
     * @return void
     */
    public function apply_headers()
    {
        if (headers_sent($file, $line)) {
            throw new ResponseException(
                "Output was already started: in file : '{$file}', on line: '{$line}'."
            );
        }

        foreach ($this->headers as $type => $header) {
            if (substr($type, 0, 1) !== '-') {
                $header = $type . ': ' . $header;
            }
            header($header);
        }
    }

    /**
     * Add header to the list of existing headers.
     * --
     * @param  mixed  $header -- Array or string
     * @param  string $type   -- Unique header type
     */
    public function header($header, $type=false)
    {
        if (!is_array($header)) { $header = [$type => $header]; }

        foreach ($header as $type => $hdr) {
            $this->headers[$type] = $hdr;
        }
    }

    /**
     * Remove all existing headers and set new one.
     * --
     * @param  mixed $header String or Array
     * @param  mixed $type   String or false
     */
    public function replace($header, $type=false)
    {
        $this->clear();
        $this->header($header, $type);
    }

    /**
     * Will remove all header set so far.
     * --
     * @return void
     */
    public function clear()
    {
        $this->headers = [];
    }

    /**
     * Return currently set headers as array.
     * --
     * @return array
     */
    public function as_array()
    {
        return $this->headers;
    }

    /**
     * Will redirect (if possible/allowed) withour any special status code.
     * ---
     * @param   string  $url    Full url address
     * @return  void
     */
    public function to($url)
    {
        $headers = [
            'Expires'       => 'Mon, 16 Apr 1984 02:40:00 GMT',
            'Last-Modified' => gmdate('D, d M Y H:i:s') . ' GMT',
            'Cache-Control' => 'no-cache, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
            'Location'      => $url,
        ];
        $this->event->trigger('/mysli/core/lib/http->redirect', $headers);
        $this->header_replace($headers);
    }

    /**
     * Standard response for successful HTTP requests.
     * --
     * @return  void
     */
    public function status_200_ok()
        { $this->header('HTTP/1.1 200 OK', '-Status'); }

    /**
     * The server successfully processed the request,
     * but is not returning any content.
     * --
     * @return  void
     */
    public function status_204_no_content()
        { $this->header('HTTP/1.1 204 No Content', '-Status'); }

    /**
     * This and all future requests should be directed to the given URI.
     * This method will ignore directive in configurations you must provide *full* URL.
     * --
     * @param   string  $url
     * @return  void
     */
    public function status_301_moved_permanently($url)
    {
        $this->header([
            '-Status'  => 'HTTP/1.1 301 Moved Permanently',
            'Location' => $url
        ]);
    }

    /**
     * In this occasion, the request should be repeated with another URI,
     * but future requests can still use the original URI.
     * In contrast to 303, the request method should not be changed
     * when reissuing the original request. For instance, a POST request
     * must be repeated using another POST request.
     * It will ignore directive in configurations you must provide *full* URL.
     * --
     * @param   string  $url
     * @return  void
     */
    public function status_307_temporary_redirect($url)
    {
        $this->header([
            '-Status'  => 'HTTP/1.1 307 Temporary Redirect',
            'Location' => $url
        ]);
    }

    /**
     * The request contains bad syntax or cannot be fulfilled.
     * --
     * @return  void
     */
    public function status_400_bad_request()
    {
        $this->header('HTTP/1.1 400 Bad Request', '-Status');
    }

    /**
     * The request requires user authentication.
     * --
     * @return void
     */
    public function status_401_unauthorized()
    {
        $this->header('HTTP/1.1 401 Unauthorized', '-Status');
    }

    /**
     * The request was a legal request, but the server is refusing to respond to it.
     * Unlike a 401 Unauthorized response, authenticating will make no difference.
     * --
     * @return  void
     */
    public function status_403_forbidden()
    {
        $this->header('HTTP/1.1 403 Forbidden', '-Status');
    }

    /**
     * The requested resource could not be found but may be available again in the future.
     * Subsequent requests by the client are permissible.
     * --
     * @return  void
     */
    public function status_404_not_found()
    {
        $this->header('HTTP/1.0 404 Not Found', '-Status');
    }

    /**
     * Indicates that the resource requested is no longer available
     * and will not be available again. This should be used when a resource
     * has been intentionally removed; however, it is not necessary to return
     * this code and a 404 Not Found can be issued instead.
     * Upon receiving a 410 status code, the client should not request
     * the resource again in the future. Clients such as search engines should
     * remove the resource from their indexes.
     * --
     * @param   string  $message
     * @param   boolean $die
     * @return  void
     */
    public function status_410_gone()
    {
        $this->header('HTTP/1.0 410 Gone', '-Status');
    }

    /**
     * The server is currently unavailable (because it is overloaded or down
     * for maintenance). Generally, this is a temporary state.
     * --
     * @return  void
     */
    public function status_503_service_unavailable()
    {
        $this->header('HTTP/1.0 503 Service Unavailable', '-Status');
    }

    public function content_type_json()
    {
        $this->header('application/json', 'Content-type');
    }
}

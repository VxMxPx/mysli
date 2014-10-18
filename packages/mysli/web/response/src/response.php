<?php

namespace mysli\web\response;

__use(__namespace__,
    'mysli/web/request',
    'mysli/framework/event'
);

class response {

    private static $headers = []; // All headers to be applied.
    private static $status = 0;        // Current status code

    /**
     * Apply headers, no way back at this point!
     * @event  mysli/web/response:apply_headers (array $headers)
     */
    static function apply_headers() {

        if (headers_sent($file, $line)) {
            throw new exception\response(
                "Output was already started in file: ".
                "'{$file}', on line: '{$line}'.");
        }

        event::trigget('mysli/web/response:apply_headers', [self::$headers]);

        foreach (self::$headers as $type => $header) {
            if (substr($type, 0, 1) !== '-') {
                $header = $type . ': ' . $header;
            }
            header($header);
        }
    }
    /**
     * Add header to the list of existing headers.
     * @param  mixed  $header array or string
     * @param  string $type   unique header type
     */
    static function header($header, $type=null) {

        if (!is_array($header)) {
            $header = [$type => $header];
        }

        foreach ($header as $type => $hdr) {
            self::$headers[$type] = $hdr;
        }
    }
    /**
     * Remove all existing headers and set new one.
     * @param  mixed $header string or array
     * @param  mixed $type   string or null
     */
    static function replace($header, $type=null) {
        self::clear();
        self::header($header, $type);
    }
    /**
     * Will remove all headers and status set so far.
     */
    static function clear() {
        self::$headers = [];
        self::$status  = 0;
    }
    /**
     * Get currently set status.
     * @return integer
     */
    static function get_status() {
        return self::$status;
    }
    /**
     * Return currently set headers as array.
     * @return array
     */
    static function as_array() {
        return self::$headers;
    }
    /**
     * Standard response for successful HTTP requests.
     */
    static function status_200_ok() {
        self::$status = 200;
        self::header(request::protocol() . ' 200 OK', '-Status');
    }
    /**
     * The server successfully processed the request,
     * but is not returning any content.
     */
    static function status_204_no_content() {
        self::$status = 204;
        self::header(request::protocol() . ' 204 No Content', '-Status');
    }
    /**
     * This and all future requests should be directed to the given URI.
     * You must provide *full* URL.
     *
     * @param  string $url
     */
    static function status_301_moved_permanently($url) {
        self::clear();
        self::$status = 301;
        self::header([
            '-Status'  => request::protocol() . ' 301 Moved Permanently',
            'Location' => $url
        ]);
    }
    /**
     * The HTTP response status code 302 Found is a common way of performing a
     * redirection. The User Agent (e.g. a web browser) is invited by a response
     * with this code to make a second, otherwise identical, request, to the new
     * URL specified in the Location field. The HTTP/1.0 specification
     * (RFC 1945) defines this code, and gives it the description phrase
     * "Moved Temporarily".
     *
     * @param  string $url
     */
    static function status_302_found($url) {
        self::clear();
        self::$status = 302;
        self::header([
            '-Status'  => request::protocol() . ' 302 Found',
            'Location' => $url
        ]);
    }
    /**
     * The HTTP response status code 303 See Other is the correct way to
     * redirect web applications to a new URI, particularly after an HTTP POST
     * has been performed, since RFC 2616 (HTTP 1.1).
     * This response indicates that the correct response can be found under a
     * different URI and should be retrieved using a GET method. The specified
     * URI is not a substitute reference for the original resource.
     *
     * @param  string $url
     */
    static function status_303_see_other($url) {
        self::clear();
        self::$status = 303;
        self::header([
            '-Status'  => request::protocol() . ' 303 See Other',
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
     *
     * @param  string $url
     */
    static function status_307_temporary_redirect($url) {
        self::clear();
        self::$status = 307;
        self::header([
            '-Status'  => request::protocol() . ' 307 Temporary Redirect',
            'Location' => $url
        ]);
    }
    /**
     * The request contains bad syntax or cannot be fulfilled.
     */
    static function status_400_bad_request() {
        self::clear();
        self::$status = 400;
        self::header(request::protocol() . ' 400 Bad Request', '-Status');
    }
    /**
     * The request requires user authentication.
     */
    static function status_401_unauthorized() {
        self::clear();
        self::$status = 401;
        self::header(request::protocol() . ' 401 Unauthorized', '-Status');
    }
    /**
     * The request was a legal request, but the server is refusing to respond
     * to it. Unlike a 401 Unauthorized response, authenticating
     * will make no difference.
     */
    static function status_403_forbidden() {
        self::clear();
        self::$status = 403;
        self::header(request::protocol() . ' 403 Forbidden', '-Status');
    }
    /**
     * The requested resource could not be found but may be available again
     * in the future. Subsequent requests by the client are permissible.
     */
    static function status_404_not_found() {
        self::clear();
        self::$status = 404;
        self::header(request::protocol() . ' 404 Not Found', '-Status');
    }
    /**
     * Indicates that the resource requested is no longer available
     * and will not be available again. This should be used when a resource
     * has been intentionally removed; however, it is not necessary to return
     * this code and a 404 Not Found can be issued instead.
     * Upon receiving a 410 status code, the client should not request
     * the resource again in the future. Clients such as search engines should
     * remove the resource from their indexes.
     */
    static function status_410_gone() {
        self::clear();
        self::$status = 410;
        self::header(request::protocol() . ' 410 Gone', '-Status');
    }
    /**
     * A generic error message, given when an unexpected condition was
     * encountered and no more specific message is suitable.
     */
    static function status_500_internal_server_error() {
        self::clear();
        self::$status = 500;
        self::header(
            request::protocol() . ' 500 Internal Server Error',
            '-Status');
    }
    /**
     * The server either does not recognize the request method, or it lacks
     * the ability to fulfill the request. Usually this implies
     * future availability (e.g., a new feature of a web-service API).
     */
    static function status_501_not_implemented() {
        self::clear();
        self::$status = 500;
        self::header(request::protocol() . ' 501 Not Implemented', '-Status');
    }
    /**
     * The server is currently unavailable (because it is overloaded or down
     * for maintenance). Generally, this is a temporary state.
     */
    static function status_503_service_unavailable() {
        self::clear();
        self::$status = 503;
        self::header(
            request::protocol() . ' 503 Service Unavailable',
            '-Status');
    }
    /**
     * Set content-type to application/json
     * --
     * @return null
     */
    static function content_type_json() {
        self::header('application/json; charset=utf-8', 'Content-type');
    }

    static function content_type_html() {
        self::header('text/html; charset=utf-8', 'Content-type');
    }
}

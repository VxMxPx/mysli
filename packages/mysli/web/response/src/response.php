<?php

namespace mysli\web\response;

__use(__namespace__, '
    mysli.web.request
    mysli.framework.event
');

class response {

    const status_200_ok                    = 200;
    const status_204_no_content            = 204;
    const status_301_moved_permanently     = 301;
    const status_302_found                 = 302;
    const status_303_see_other             = 303;
    const status_307_temporary_redirect    = 307;
    const status_400_bad_request           = 400;
    const status_401_unauthorized          = 401;
    const status_403_forbidden             = 403;
    const status_404_not_found             = 404;
    const status_410_gone                  = 410;
    const status_500_internal_server_error = 500;
    const status_501_not_implemented       = 501;
    const status_503_service_unavailable   = 503;

    const ctype_json  = 'application/json';
    const ctype_pdf   = 'application/pdf';
    const ctype_xml   = 'application/xml';
    const ctype_gzip  = 'application/gzip';
    const ctype_gif   = 'image/gif';
    const ctype_jpeg  = 'image/jpeg';
    const ctype_png   = 'image/png';
    const ctype_svg   = 'image/svg+xml';
    const ctype_plain = 'text/plain';
    const ctype_html  = 'text/html';

    private static $statuses = [
        // Standard response for successful HTTP requests.
        200 => 'OK',
        // The server successfully processed the request,
        // but is not returning any content.
        204 => 'No Content',
        // This and all future requests should be directed to the given URI.
        //      Location is required.
        301 => 'Moved Permanently',
        // The HTTP response status code 302 Found is a common way of performing
        // a redirection. The User Agent (e.g. a web browser) is invited by a
        // response with this code to make a second, otherwise identical,
        // request, to the new URL specified in the Location field.
        // The HTTP/1.0 specification (RFC 1945) defines this code,
        // and gives it the description phrase "Moved Temporarily".
        //      Location is required.
        302 => 'Found',
        // The HTTP response status code 303 See Other is the correct way to
        // redirect web applications to a new URI, particularly after an HTTP
        // POST has been performed, since RFC 2616 (HTTP 1.1). This response
        // indicates that the correct response can be found under a different
        // URI and should be retrieved using a GET method. The specified URI is
        // not a substitute reference for the original resource.
        //      Location is required.
        303 => 'See Other',
        // In this occasion, the request should be repeated with another URI,
        // but future requests can still use the original URI.
        // In contrast to 303, the request method should not be changed
        // when reissuing the original request. For instance, a POST request
        // must be repeated using another POST request.
        //      Location is required.
        307 => 'Temporary Redirect',
        // The request contains bad syntax or cannot be fulfilled.
        400 => 'Bad Request',
        // The request requires user authentication.
        401 => 'Unauthorized',
        // The request was a legal request, but the server is refusing to
        // respond to it. Unlike a 401 Unauthorized response, authenticating
        // will make no difference.
        403 => 'Forbidden',
        // The requested resource could not be found but may be available again
        // in the future. Subsequent requests by the client are permissible.
        404 => 'Not Found',
        // Indicates that the resource requested is no longer available
        // and will not be available again. This should be used when a resource
        // has been intentionally removed; however, it is not necessary to
        // return this code and a 404 Not Found can be issued instead.
        // Upon receiving a 410 status code, the client should not request
        // the resource again in the future. Clients such as search engines
        // should remove the resource from their indexes.
        410 => 'Gone',
        // A generic error message, given when an unexpected condition was
        // encountered and no more specific message is suitable.
        500 => 'Internal Server Error',
        // The server either does not recognize the request method, or it lacks
        // the ability to fulfill the request. Usually this implies
        // future availability (e.g., a new feature of a web-service API).
        501 => 'Not Implemented',
        // The server is currently unavailable (because it is overloaded or down
        // for maintenance). Generally, this is a temporary state.
        503 => 'Service Unavailable',
    ];

    private static $headers = []; // All headers to be applied.
    private static $status = 0;   // Current status code

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

        event::trigger('mysli/web/response:apply_headers', [self::$headers]);

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
    static function set_header($header, $type=null) {

        if (!is_array($header)) {
            $header = [$type => $header];
        }

        foreach ($header as $type => $hdr) {
            self::$headers[$type] = $hdr;
        }
    }
    /**
     * Get particular header if set.
     * If no id provided, all headers will be returned as an array.
     * @param  string $id
     * @return mixed string|array
     */
    static function get_header($id=null) {
        if (!$id) {
            return self::$headers;
        } else {
            if (array_key_exists($id, self::$headers)) {
                return self::$headers[$id];
            }
        }
    }
    /**
     * Remove all existing headers and set new one.
     * @param  mixed $header string or array
     * @param  mixed $type   string or null
     */
    static function replace($header, $type=null) {
        self::clear();
        self::set_header($header, $type);
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
     * Set particular status by code...
     * @param integer $status
     * @param string  $location
     */
    static function set_status($status, $location=null) {

        $status = (int) $status;

        if (!isset(self::$statuses[$status])) {
            throw new framework\exception\argument(
                "Not a valid status: `{$status}`");
        }

        self::$status = $status;

        self::set_header(
            request::protocol().' '.$status.' '.self::$statuses[$status],
            '-Status');

        if ($location) {
            self::set_header($location, 'Location');
        }
    }
    /**
     * Get content-type header.
     * @return string
     */
    static function get_content_type() {
        return self::get_header('Content-type');
    }
    /**
     * Set content-type header
     * @param string $type
     * @param string $charset
     */
    static function set_content_type($type, $charset='utf-8') {
        self::set_header("{$type}; charset={$charset}", 'Content-type');
    }
}

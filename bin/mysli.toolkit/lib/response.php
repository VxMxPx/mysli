<?php

namespace mysli\toolkit; class response
{
    const __use = '
        .{ request, event, log, exception.response }
    ';

    /*
    Static codes.
     */
    const status_200_ok                    = 200;
    const status_204_no_content            = 204;
    const status_301_moved_permanently     = 301; // Location!
    const status_302_found                 = 302; // Location!
    const status_303_see_other             = 303; // Location!
    const status_307_temporary_redirect    = 307; // Location!
    const status_400_bad_request           = 400;
    const status_401_unauthorized          = 401;
    const status_403_forbidden             = 403;
    const status_404_not_found             = 404;
    const status_410_gone                  = 410;
    const status_500_internal_server_error = 500;
    const status_501_not_implemented       = 501;
    const status_503_service_unavailable   = 503;

    /*
    Content type codes.
     */
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

    /**
     * Translate status from numeric to string representation.
     * --
     * @var array
     */
    private static $statuses = [
        200 => 'OK',
        204 => 'No Content',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        410 => 'Gone',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        503 => 'Service Unavailable',
    ];

    /**
     * All headers to be applied.
     * --
     * @var array
     */
    private static $headers = [];

    /**
     * Current status code.
     * --
     * @var integer
     */
    private static $status = 0;

    /**
     * Apply headers, no way back at this point!
     * --
     * @event toolkit.response::apply_headers(array $headers)
     * --
     * @throws mysli\framework\exception\response
     *         10 Output was already started.
     */
    static function apply_headers()
    {
        if (headers_sent($file, $line))
        {
            throw new exception\response(
                "Output was already started in file: ".
                "'{$file}', on line: '{$line}'.", 10
            );
        }

        event::trigger('toolkit.response::apply_headers', [static::$headers]);

        foreach (static::$headers as $type => $header)
        {
            if (substr($type, 0, 1) !== '-')
            {
                $header = $type . ': ' . $header;
            }

            \log::info("Header: `{$header}`.", __CLASS__);
            header($header);
        }
    }

    /**
     * Add header to the list of existing headers.
     * --
     * @param mixed  $header Array or string.
     * @param string $type   Unique header type.
     */
    static function set_header($header, $type=null)
    {
        if (!is_array($header))
        {
            $header = [$type => $header];
        }

        foreach ($header as $type => $hdr)
        {
            static::$headers[$type] = $hdr;
        }
    }

    /**
     * Get particular header if set.
     * If no id provided, all headers will be returned as an array.
     * --
     * @param string $id
     * --
     * @return mixed string|array
     */
    static function get_header($id=null)
    {
        if (!$id)
        {
            return static::$headers;
        }
        else
        {
            if (array_key_exists($id, static::$headers))
            {
                return static::$headers[$id];
            }
        }
    }

    /**
     * Remove all existing headers and set new one.
     * --
     * @param mixed $header String or array.
     * @param mixed $type   String or null.
     */
    static function replace($header, $type=null)
    {
        static::clear();
        static::set_header($header, $type);
    }

    /**
     * Remove all headers and status set so far.
     */
    static function clear()
    {
        static::$headers = [];
        static::$status  = 0;
    }

    /**
     * Get currently set status.
     * --
     * @return integer
     */
    static function get_status()
    {
        return static::$status;
    }

    /**
     * Set particular status by code.
     * Some statuses need location.
     * --
     * @param integer $status    (@see static::$statuses)
     * @param string  $location
     * --
     * @throws mysli\toolkit\exception\response 10 Not a valid status.
     */
    static function set_status($status, $location=null)
    {
        $status = (int) $status;

        if (!isset(static::$statuses[$status]))
        {
            throw new exception\response(
                "Not a valid status: `{$status}`", 10
            );
        }

        static::$status = $status;

        static::set_header(
            request::protocol().' '.$status.' '.static::$statuses[$status],
            '-Status'
        );

        if ($location)
        {
            static::set_header($location, 'Location');
        }
    }

    /**
     * Get currently set content-type header.
     * --
     * @return string
     */
    static function get_content_type()
    {
        return static::get_header('Content-type');
    }

    /**
     * Set content-type header.
     * --
     * @param string $type
     * @param string $charset
     */
    static function set_content_type($type, $charset='utf-8')
    {
        static::set_header("{$type}; charset={$charset}", 'Content-type');
    }
}

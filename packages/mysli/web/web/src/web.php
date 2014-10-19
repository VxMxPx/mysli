<?php

namespace mysli\web\web;

__use(__namespace__,
    'mysli/framework/event',
    'mysli/framework/fs',
    'mysli/web/response',
    'mysli/web/request',
    'mysli/util/output',
    'mysli/util/config'
);

class web {
    /**
     * Routing!
     */
    static function route() {
        // Get route and remove any * < > character.
        $route = implode('/', request::segment());
        $route = str_replace(['*', '<', '>'], '', $route);
        $method = strtolower(request::method());

        event::trigger("mysli/web/web:route<{$method}><$route>");

        if (response::get_status() === 0) {
            response::status_404_not_found();
        }

        if (response::get_status() === 404) {
            event::trigger('mysli/web/web:404');
        }
    }
    /**
     * Apply headers and set output.
     * @param string $output
     */
    static function output(&$output) {

        if (response::get_status() === 0) {
            response::status_200_ok();
        }
        response::apply_headers();

        $output  = is_string($output) ? $output : '';
        $output .= output::as_html();
    }
    /**
     * Get absolute public path.
     * @param  string ... Append to the path.
     * @return string
     */
    static function path() {
        if (!defined('MYSLI_PUBPATH')) {
            $pubpath = realpath(
                fs::datpath(config::select('mysli/web/web', 'relative_path')));
        } else {
            $pubpath = MYSLI_PUBPATH;
        }
        $arguments = func_get_args();
        $arguments = implode(DIRECTORY_SEPARATOR, $arguments);
        return fs::ds($pubpath, $arguments);
    }
    /**
     * Get URL, with appended URI (if so desired).
     * This will try to read url setting from config, and if not found, it will
     * use request::host()
     * @param  string $uri
     * @return string
     */
    static function url($uri=null) {
        $url = config::get('mysli/web/web', 'url', request::host());
        $url = rtrim($url, '/') . '/'; // Always add ending slash!
        if ($uri) {
            $url .= ltrim($uri, '/');
        }

        return $url;
    }
}

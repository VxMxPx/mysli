<?php

namespace Mysli\Core\Lib;

class Error
{
    // Error codes to plain English string.
    private static $error_to_title =  [
        E_ERROR              => 'Error',
        E_WARNING            => 'Warning',
        E_PARSE              => 'Parsing Error',
        E_NOTICE             => 'Notice',
        E_CORE_ERROR         => 'Core Error',
        E_CORE_WARNING       => 'Core Warning',
        E_COMPILE_ERROR      => 'Compile Error',
        E_COMPILE_WARNING    => 'Compile Warning',
        E_USER_ERROR         => 'User Error',
        E_USER_WARNING       => 'User Warning',
        E_USER_NOTICE        => 'User Notice',
        E_STRICT             => 'Runtime Notice',
        E_RECOVERABLE_ERROR  => 'Catchable Fatal Error',
        E_DEPRECATED         => 'Run-time notice',
        E_USER_DEPRECATED    => 'User-generated warning message',
    ];

    private static $map = [
        E_ERROR              => 'err',
        E_WARNING            => 'war',
        E_PARSE              => 'err',
        E_NOTICE             => 'war',
        E_CORE_ERROR         => 'err',
        E_CORE_WARNING       => 'war',
        E_COMPILE_ERROR      => 'err',
        E_COMPILE_WARNING    => 'war',
        E_USER_ERROR         => 'err',
        E_USER_WARNING       => 'war',
        E_USER_NOTICE        => 'war',
        E_STRICT             => 'war',
        E_RECOVERABLE_ERROR  => 'err',
        E_DEPRECATED         => 'war',
        E_USER_DEPRECATED    => 'war',
    ];

    private static $template = '
        <!DOCTYPE html>
        <html lang="en">
        <meta charset=utf-8>
        <title>Fatal Error!</title>
        <style>
            *       { padding: 0; margin: 0; line-height: 1.5em; }
            ::selection      { background-color: #47c; color: #eee; }
            ::-moz-selection { background-color: #47c; color: #eee; }
            body    { background-color: #111; color: #bbb; font-size: 16px; font-family: "Sans", sans-serif; }
            h1, h2  { font-family: "Serif", serif; font-weight: normal; }
            h2      { padding-top: 30px; padding-bottom: 4px; margin-bottom: 4px; border-bottom: 1px dotted #ddd; }
            a       { color: #47c; padding: 2px; }
            a:hover { background-color: #47c; color: #fff; text-decoration: none; border-radius: 4px; }
            code    { font-family: "Monospace", monospace; background-color: #f2f2f2; color: #224; }
            .fade   { color: #555; font-style: italic; }
            #page   { width: 800px; margin: 20px auto; padding: 20px; }
            #log    { padding-top: 10px; margin-top: 5px; }
            #log > div { box-shadow: 0 0 8px #060606; border-radius: 4px; }
            #log > div > div:first-child { border-radius: 4px 4px 0 0; }
            #log > div > div:last-child  { border-radius: 0 0 4px 4px; border-bottom: none !important; }
        </style>
        <div id=page>
            <h1>Something went wrong</h1>
            <div id=log>
                <pre>{{error_report}}</pre>';

    public static function handle($errno, $errmsg, $filename, $linenum)
    {
        // Get error title
        $title = isset(self::$error_to_title[$errno]) ? self::$error_to_title[$errno] : 'Unknown';
        $errmsg = $title . ":\n" . $errmsg;

        // Get error simple type
        $type = isset(self::$map[$errno]) ? self::$map[$errno] : 'war';

        Log::add($errmsg, $type, __FILE__, __LINE__);

        // Fatal error.
        if ($type === 'err')
        {
            // Error reporting
            $error_report = Log::as_string();

            Event::trigger('/mysli/core/lib/error::handle', $error_report);

            if (is_cli()) {
                die($error_report);
            }
            else {
                die(str_replace(
                    array('{{error_report}}', '{{error_no}}'),
                    array($error_report, $errno),
                    self::$template));
            }
        }
    }
}
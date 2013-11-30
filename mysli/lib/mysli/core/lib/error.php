<?php

namespace Mysli\Core\Lib;

class Error
{
    // Error codes to plain English string.
    private $error_to_title =  [
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

    private $map = [
        E_ERROR              => 'error',
        E_WARNING            => 'warn',
        E_PARSE              => 'error',
        E_NOTICE             => 'warn',
        E_CORE_ERROR         => 'error',
        E_CORE_WARNING       => 'warn',
        E_COMPILE_ERROR      => 'error',
        E_COMPILE_WARNING    => 'warn',
        E_USER_ERROR         => 'error',
        E_USER_WARNING       => 'warn',
        E_USER_NOTICE        => 'warn',
        E_STRICT             => 'warn',
        E_RECOVERABLE_ERROR  => 'error',
        E_DEPRECATED         => 'warn',
        E_USER_DEPRECATED    => 'warn',
    ];

    private $log_template = '<div class="log_item log_{type}"><p>{date_time} - {type}</p><code><pre>{message}</pre></code><p>{file} - {line}</p></div>';

    private $template = '
        <!DOCTYPE html>
        <html lang="en">
        <meta charset=utf-8>
        <title>Fatal Error!</title>
        <style>
            *       { padding: 0; margin: 0; line-height: 1.5em; }
            body    { background-color: #112; color: #ddd; font-size: 16px; font-family: "Monospace", monospace; }
            h1      { font-weight: normal; margin-bottom: 20px; font-size: 32px; }
            code pre { background-color: #001; padding: 10px; }
            pre     { white-space: pre-wrap; }
            #page   { width: 800px; margin: 20px auto; padding: 20px; }
            .log_item { width: 100%; margin-bottom: 20px; background-color: #050515; }
            .log_item > p { color: #778; font-size: 14px; padding: 10px; }
            .log_item.log_info  { color: #6f6; }
            .log_item.log_error { color: #f33; }
            .log_item.log_warn  { color: #fa3; }
        </style>
        <div id=page>
            <h1>Fatal Error! :(</h1>
            <div id=log>
                {{error_report}}';

    protected $log;
    protected $event;

    /**
     * Construct ERROR
     * --
     * @param array $config
     *   - none
     * @param array $dependencies
     *   - log
     *   - event
     */
    public function __construct(array $config = [], array $dependencies = [])
    {
        $this->log = $dependencies['log'];
        $this->event = $dependencies['event'];
    }

    public function handle($errno, $errmsg, $filename, $linenum)
    {
        // Get error title
        $title = isset($this->error_to_title[$errno])
            ? $this->error_to_title[$errno]
            : 'Unknown';
        $errmsg = $title . ":\n" . $errmsg;

        // Get error simple type
        $type = isset($this->map[$errno]) ? $this->map[$errno] : 'warn';

        if ($type === 'error') {
            $e = new \Exception();
            $errmsg .= "\n\nDebug backtrace:\n" . $e->getTraceAsString();
        }

        $this->log->add($errmsg, $type, $filename, $linenum);

        // Fatal error.
        if ($type === 'error')
        {
            // Error reporting
            $error_report = is_cli()
                ? $this->log->as_string()
                : $this->log->as_html($this->log_template);

            $this->event->trigger('/mysli/core/lib/error->handle/error', $error_report);

            if (is_cli()) {
                die($error_report);
            }
            else {
                die(str_replace(
                    array('{{error_report}}', '{{error_no}}'),
                    array($error_report, $errno),
                    $this->template));
            }
        }
    }
}

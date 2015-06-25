<?php

namespace mysli\toolkit; class toolkit_init
{
    /**
     * Initialize toolkit.
     * --
     * @param  string $apppath Absolute application root path.
     * @param  string $binpath Absolute binaries root path.
     * @param  string $pubpath Absolute public path.
     */
    static function __init($apppath, $binpath, $pubpath)
    {
        /*
        Define full absolute paths.
         */
        define('MYSLI_APPPATH', rtrim($apppath, '/'));
        define('MYSLI_BINPATH', rtrim($binpath, '/'));
        define('MYSLI_PUBPATH', rtrim($pubpath, '/'));

        define('MYSLI_TMPPATH', MYSLI_APPPATH.'/tmp');
        define('MYSLI_CFGPATH', MYSLI_APPPATH.'/configuration');

        /*
        Load common basic functions and classes.
         */

        // Toolkit common utilities.
        include __DIR__."/toolkit.common.php";

        // Core exceptions.
        foreach (scandir(__DIR__.'/exception') as $exception)
        {
            if (substr($exception, 0, -4) === '.php')
                include __DIR__."/exception/{$exception}";
        }

        // Toolkit logger
        include __DIR__."/log.php";
        log::info('Toolkit init, log loaded!', __CLASS__);

        // Load pkg, basic packages manager
        include __DIR__."/pkg.php";
        pkg::__init(MYSLI_CFGPATH."/toolkit.pkg.list");
        log::debug(
            "Got following packages: ".implode(', ', pkg::list_all()),
            __CLASS__
        );

        // Load autoloader
        include __DIR__."/autoloader.php";
        spl_autoload_register('\mysli\toolkit\autoloader::load', true, true);

        // Toolkit core class
        include __DIR__."/toolkit.php";

        /*
        Init str
         */
        type\str::encoding('UTF-8');

        /*
        Init request
         */
        request::__init();

        /*
        Trigger main event - system __init
         */
        event::__init(MYSLI_CFGPATH."/toolkit.events.json");
        event::trigger("toolkit::__init");

        /*
        Resolve route(s)
         */
        router::resolve();

        /*
        Apply header and send output.
         */
        log::debug("About to apply headers and output!", __CLASS__);
        response::apply_headers();
        $output = output::as_html();

        event::trigger("toolkit::__init.output", [$output]);
        echo $output;

        /*
        Close with a normal shutdown
         */
        toolkit::shutdown();
    }
}

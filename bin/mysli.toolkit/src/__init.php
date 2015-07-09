<?php

namespace mysli\toolkit; class __init
{
    /**
     * Initialize toolkit.
     * --
     * @param  string $apppath Absolute application root path.
     * @param  string $binpath Absolute binaries root path.
     * @param  string $pubpath Absolute public path.
     * --
     * @event toolkit::__init ()
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
        include __DIR__."/__common.php";

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
        Trigger main event - system __init
         */
        event::trigger("toolkit::__init");
    }

    /**
     * Run toolkit web mode, call when execute from public index.
     * --
     * @event toolkit::web ()
     * @event toolkit::web.output ( string $output )
     */
    static function web()
    {
        /*
        Define current mode to be `web`.
         */
        define('TOOLKIT_MODE', 'web');

        /*
        Trigger event - system web
         */
        event::trigger("toolkit::web");

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

        event::trigger("toolkit::web.output", [$output]);
        echo $output;

        /*
        Close with a normal shutdown
         */
        toolkit::shutdown();
    }

    /**
     * Run toolkit cli mode, run when exececuted from command line.
     * --
     * @param array $arguments
     * --
     * @event toolkit::cli ( array $arguments )
     */
    static function cli(array $arguments=[])
    {
        $help =
        "<title>Dot Utility for Mysli Platform</title>\n\n".
        "Usage: dot <command> [options...]\n".
        "You can always use dot <command> -h to get help for a specific command.\n".
        "List of available commands:\n\n".
        "<ul>{list}</ul>";

        /*
        Define current mode to be `cli`.
         */
        define('TOOLKIT_MODE', 'cli');

        /*
        Trigger event - system cli
         */
        event::trigger("toolkit::cli", [$arguments]);

        /*
        Get list of available scripts.
         */
        $scrips = pkg::list_cli();

        /*
        Get current script from arguments.
         */
        $script = array_shift($arguments);

        /*
        Check if we have script.
         */
        if (!$script || $script === '-h' || $script === '--help')
        {
            dot\ui::t($help, array_keys($scripts));
            toolkit::shutdown();
        }

        // Check if script exists.
        if (!isset($scripts[$script]))
        {
            // If it's in array, that means full absolute name was provided.
            if (!in_array($script, $scripts))
            {
                ui::warn("Invalid command! Use `-h` to see list available commands");
                toolkit::shutdown(1);
            }
        }
        else
        {
            // Set full absolute script name
            $script = $scripts[$script];
        }

        // Run script, it should be autoloaded
        try
        {
            $r = call_user_func(
                [str_replace('.', '\\', $script), '__run'],
                array_slice($arguments, 2)
            );
            toolkit::shutdown($r ? 0 : 1);
        }
        catch (\Exception $e)
        {
            dot\ui::error(
                "Error when trying to run a script `{$script}`: ".
                $e->getMessage()
            );
            toolkit::shutdown(2);
        }
    }
}
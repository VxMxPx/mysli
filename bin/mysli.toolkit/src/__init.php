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
        This might be called from PHAR, so adjust root DIR accordingly.
         */
        if (substr(__FILE__, -5) === '.phar')
            define('__RDIR__', realpath('phar://'.__FILE__));
        else
            define('__RDIR__', __DIR__);

        /*
        Load common basic functions and classes.
         */

        // Toolkit common utilities.
        include __RDIR__."/__common.php";

        // Core exceptions.
        foreach (scandir(__RDIR__.'/exception') as $exception)
        {
            if (substr($exception, 0, -4) === '.php')
                include __RDIR__."/exception/{$exception}";
        }

        // Toolkit logger
        include __RDIR__."/log.php";
        log::info('Toolkit init, log loaded!', __CLASS__);

        // Load pkg, basic packages manager
        include __RDIR__."/pkg.php";
        pkg::__init();
        log::debug(
            "Got following packages: ".implode(', ', pkg::list_all()),
            __CLASS__
        );

        // Load autoloader
        include __RDIR__."/autoloader.php";
        spl_autoload_register('\mysli\toolkit\autoloader::load', true, true);

        // Toolkit core class
        include __RDIR__."/toolkit.php";

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
        "\n<title>Dot Utility for Mysli Platform</title>\n\n".
        "Usage: dot <command> [options...]\n".
        "You can always use dot <command> -h to get help for a specific command.\n".
        "List of available commands:\n".
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
        $scripts = pkg::list_cli();

        /*
        Get current script from arguments.
         */
        $script = array_shift($arguments);

        /*
        Check if we have script.
         */
        if (!$script || $script === '-h' || $script === '--help')
        {
            \dot\ui::t($help, ['list' => array_keys($scripts)]);
            toolkit::shutdown();
        }

        // Check if script exists.
        if (!isset($scripts[$script]))
        {
            // If it's in array, that means full absolute name was provided.
            if (!in_array($script, $scripts))
            {
                \dot\ui::warn(
                    "Invalid command! Use `-h` to see list available commands"
                );
                toolkit::shutdown(1);
            }
        }
        else
        {
            // Set full absolute script name
            $script = $scripts[$script];
        }

        // Resolve script's name
        $scriptr = $script;
        $scriptr = substr_replace($scriptr, '.cli', strrpos($scriptr, '.'), 0);
        $scriptr = str_replace('.', '\\', $scriptr);

        // Check if script has __run method
        if (!method_exists($scriptr, '__run'))
        {
            \dot\ui::error("Script has no __run method: `{$script}`.");
            toolkit::shutdown(2);
        }

        // Run script
        try
        {
            $r = call_user_func([$scriptr, '__run'], $arguments);
            toolkit::shutdown($r ? 0 : 1);
        }
        catch (\Exception $e)
        {
            \dot\ui::error(
                "Error when trying to run a script `{$script}`!\n".
                $e->getMessage()
            );
            toolkit::shutdown(3);
        }
    }
}

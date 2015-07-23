<?php

/**
 * # Init
 *
 * Toolkit initialization class. It will include autoloader and other
 * core classes that needs to be loaded manually and re necessary for the
 * Toolkit to function properly.
 *
 */
namespace mysli\toolkit; class __init
{
    /**
     * Initialize toolkit.
     * --
     * @param string $apppath Absolute application root path.
     * @param string $binpath Absolute binaries root path.
     * @param string $pubpath Absolute public path.
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
        include __RDIR__."/exception/base.php";
        foreach (scandir(__RDIR__.'/exception') as $exception)
        {
            if (substr($exception, -4) === '.php' && $exception !== 'base.php')
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
     * Run toolkit web mode, call when executed from public index.
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
     * Run toolkit CLI mode, run when executed from the command line.
     * --
     * @param array $arguments
     * --
     * @event toolkit::cli ( array $arguments )
     */
    static function cli(array $arguments=[])
    {
        $help =
        "\n<title>Command Line Utility for Mysli Platform</title>\n\n".
        "Usage: mysli <command> [options...]\n".
        "You can always use mysli <command> -h to get help for a specific command.\n".
        "List of available commands:\n".
        "\n<ul>{list}</ul>\n";

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
        Check requested script exists.
         */
        if (!$script || $script === '-h' || $script === '--help')
        {
            cli\ui::t($help, ['list' => array_keys($scripts)]);
            toolkit::shutdown();
        }

        // Check weather script exists in its short form.
        if (isset($scripts[$script]))
        {
            // Set full absolute script name
            $script = $scripts[$script];
        }
        else
        {
            // If it's in array, that means full absolute name was provided.
            // In such case, nothing else needs to be done at this point.
            // If not however, that means requested script doesn't exists.
            if (!in_array($script, $scripts))
            {
                cli\ui::nl();
                cli\ui::warning(
                    "Invalid command! Use `-h` to see list available commands."
                );
                cli\ui::nl();
                toolkit::shutdown(1);
            }

        }

        // Resolve script's name
        $scriptr = $script;

        // Script is called as vendor.package.script, hence, `root.script` part
        // needs to be inserted, so that we get full class name:
        // vendor\package\root\script\script
        $scriptr = substr_replace($scriptr, '.root.script', strrpos($scriptr, '.'), 0);
        $scriptr = str_replace('.', '\\', $scriptr);

        // Try to autoload class...
        if (!class_exists($scriptr))
        {
            cli\ui::nl();
            cli\ui::error("Couldn't find class for: `{$script}`.");
            cli\ui::nl();
            toolkit::shutdown(2);
        }

        // If script's __run method is missing, nothing but error can be done.
        // All scripts needs __run method as an entry point.
        if (!method_exists($scriptr, '__run'))
        {
            cli\ui::nl();
            cli\ui::error("Script has no __run method: `{$script}`.");
            cli\ui::nl();
            toolkit::shutdown(2);
        }

        // Try to Run script
        try
        {
            cli\ui::nl();
            $r = call_user_func([$scriptr, '__run'], $arguments);
            cli\ui::nl();
            // Grab result and shutdown system with it.
            // If result was false, that mean there was a problem,
            // hence exit with `1`
            toolkit::shutdown($r ? 0 : 1);
        }
        catch (\Exception $e)
        {
            if ($e->getCode() > -99)
            {
                cli\ui::nl();
                cli\ui::error(
                    "Error when trying to run a script `{$script}`!\n".
                    $e->getMessage()
                );
                cli\ui::nl();
                toolkit::shutdown(3);
            }
            else
                throw $e;
        }
    }
}

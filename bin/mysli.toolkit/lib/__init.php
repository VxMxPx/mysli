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
        define('MYSLI_CNTPATH', MYSLI_APPPATH.'/content');

        /*
        Weather to display errors.
         */
        ini_set('display_errors', TOOLKIT_DISPLAY_ERRORS);

        /*
        See if we need to change the default timezone.
         */
        if (TOOLKIT_DEFAULT_TIMEZONE)
            date_default_timezone_set(TOOLKIT_DEFAULT_TIMEZONE);

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
        $logger = toolkit_core_loader(TOOLKIT_LOG, MYSLI_BINPATH);
        class_alias($logger, 'log');
        log::__init();
        log::info('Toolkit init, log loaded!', __CLASS__);

        // Load pkg, basic packages manager
        $pkg = toolkit_core_loader(TOOLKIT_PKG, MYSLI_BINPATH);
        class_alias($pkg, 'pkg');
        pkg::__init();

        log::debug(
            "Got following packages: ".implode(', ', pkg::list_all()), __CLASS__
        );

        // Load autoloader
        $autoloader = toolkit_core_loader(TOOLKIT_AUTOLOAD, MYSLI_BINPATH);
        class_alias($autoloader, 'autoloader');
        spl_autoload_register("{$autoloader}::load", true, true);

        // Toolkit core class
        $toolkit = toolkit_core_loader(TOOLKIT_CORE, MYSLI_BINPATH);
        class_alias($toolkit, 'toolkit');

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
        $route = '/'.implode('/', request::segment());
        $r = ($route === '/')
            ? route::index()
            : route::execute($route);

        if (!$r)
        {
            if (!route::error(404))
            {
                log::debug(
                    "Page not found for: `{$route}`, additionally, ".
                    "no *error route is available.", __CLASS__);
                response::set_status(response::status_404_not_found);
                event::trigger('toolkit::web.route_error');
            }
        }

        /*
        Apply header and send output.
         */
        log::debug("About to apply headers and output!", __CLASS__);
        response::apply_headers();
        $output = output::as_html();

        event::trigger("toolkit::web.output", [ $output ]);
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
            cli\ui::t($help, ['list' => array_column($scripts, 'script')]);
            toolkit::shutdown();
        }

        /*
        Falsify script class to check later weather it was found.
         */
        $script_class = false;

        /*
        Check all scripts for current
         */
        foreach ($scripts as $id => $meta)
        {
            if ($id === $script || $meta['script'] === $script)
            {
                $script_class = $meta['class'];
                break;
            }
        }

        // Check weather script class was found.
        if (!$script_class)
        {
            cli\ui::nl();
            cli\ui::warning(
                "Invalid command! Use `-h` to see list available commands."
            );
            cli\ui::nl();
            toolkit::shutdown(1);
        }

        // Try to autoload class...
        if (!class_exists($script_class))
        {
            cli\ui::nl();
            cli\ui::error("Couldn't find class for: `{$script}`.");
            cli\ui::nl();
            toolkit::shutdown(2);
        }

        // If script's __run method is missing, nothing but error can be done.
        // All scripts needs __run method as an entry point.
        if (!method_exists($script_class, '__run'))
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
            $r = call_user_func([$script_class, '__run'], $arguments);
            cli\ui::nl();
            // Grab result and shutdown system with it.
            // If result was false, that mean there was a problem,
            // hence exit with `1`
            toolkit::shutdown($r ? 0 : 1);
        }
        catch (\Exception $e)
        {
            cli\ui::nl();
            cli\ui::error(
                "Error when trying to run a script `{$script}`!\n".
                $e->getMessage()
            );
            cli\ui::nl();

            // Debug mode?
            if (MYSLI_ROOT_DEBUG)
                cli\ui::line($e->getTraceAsString());

            toolkit::shutdown(3);
        }
    }
}

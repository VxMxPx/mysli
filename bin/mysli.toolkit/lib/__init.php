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
        Start time
         */
        if (isset($_SERVER['REQUEST_TIME_FLOAT']))
        {
            define('MYSLI_BOOT_AT', $_SERVER['REQUEST_TIME_FLOAT']);
        }
        else
        {
            $mtime = microtime();
            $mtime = explode(' ', $mtime, 2);
            define('MYSLI_BOOT_AT', $mtime[0]+$mtime[1]);
        }

        /*
        Start memory
         */
        define('MYSLI_BOOT_MEMORY', memory_get_usage());

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
        \log::__init();
        \log::info('Toolkit init, log loaded!', __CLASS__);

        // Load pkg, basic packages manager
        $pkg = toolkit_core_loader(TOOLKIT_PKG, MYSLI_BINPATH);
        class_alias($pkg, 'pkg');
        pkg::__init();

        \log::debug(
            "Got following packages: ".implode(', ', pkg::list_all()), __CLASS__
        );

        // Load autoloader
        $autoloader = toolkit_core_loader(TOOLKIT_AUTOLOAD, MYSLI_BINPATH);
        class_alias($autoloader, 'autoloader');
        spl_autoload_register("{$autoloader}::load", true, true);
        \log::debug("Autoloader was registered: `{$autoloader}::load`.", __CLASS__);

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
                \log::debug(
                    "Page not found for: `{$route}`, additionally, ".
                    "no *error route is available.", __CLASS__);
                response::set_status(response::status_404_not_found);
                event::trigger('toolkit::web.route_error');
            }
        }

        /*
        Apply header and send output.
         */
        \log::debug("About to apply headers and output!", __CLASS__);
        $output = output::as_html();

        if (!response::get_header('Content-Length') && !TOOLKIT_PRINT_LOG)
        {
            // Keep strlen as we're messuring bytes
            response::set_header(str::bytes($output), 'Content-Length');
        }

        if (!response::get_header('Content-Type'))
        {
            response::set_header('text/html; charset=utf-8', 'Content-Type');
        }

        response::apply_headers();

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
            cli\ui::title("Command Line Utility for Mysli Platform\n");
            cli\ui::line("Usage: mysli <command> [options...]\n");
            cli\ui::line(
                "You can always use mysli <command> -h ".
                "to get help for a specific command.");
            cli\ui::line("List of available commands:");
            cli\ui::list(array_column($scripts, 'script'));
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
            cli\ui::warning(
                'WARNING',
                "Invalid command! Use `-h` to see list available commands.");
            toolkit::shutdown(1);
        }

        // Try to autoload class...
        if (!class_exists($script_class))
        {
            cli\ui::error('ERROR', "Couldn't find class for: `{$script}`.");
            toolkit::shutdown(2);
        }

        // If script's __run method is missing, nothing but error can be done.
        // All scripts needs __run method as an entry point.
        if (!method_exists($script_class, '__run'))
        {
            cli\ui::error('ERROR', "Script has no __run method: `{$script}`.");
            toolkit::shutdown(2);
        }

        // Try to Run script
        try
        {
            $r = call_user_func([$script_class, '__run'], $arguments);
            // Grab result and shutdown system with it.
            // If result was false, that mean there was a problem,
            // hence exit with `1`
            toolkit::shutdown($r ? 0 : 1);
        }
        catch (\Exception $e)
        {
            cli\ui::error(
                'ERROR',
                "Error when trying to run a script `{$script}`!\n".
                $e->getMessage()
            );

            // Debug mode?
            if (MYSLI_ROOT_DEBUG)
            {
                cli\ui::line($e->getTraceAsString());
            }

            toolkit::shutdown(3);
        }
    }
}

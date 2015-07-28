<?php

/**
 * # Util
 *
 * Various utilities used in command line interface.
 */
namespace mysli\toolkit\cli; class util
{
    const __use = '.cli.output';

    /**
     * Detect terminal width.
     * --
     * @return integer
     */
    static function terminal_width()
    {
        if (!static::is_cli())
        {
            return 80;
        }
        elseif (!static::is_win())
        {
            return static::execute('tput cols');
        }
        else
        {
            $result = static::popen('mode');
            preg_match('/^ *Columns\: *([0-9]+)$/m', $result, $matches);
            return $matches[1];
        }
    }

    /**
     * Determine if script is running in command line interface.
     * --
     * @return boolean
     */
    static function is_cli()
    {
        return php_sapi_name() === 'cli' || defined('STDIN');
    }

    /**
     * Check if script is running in windows environment.
     * --
     * @return boolean
     */
    static function is_win()
    {
        return strtoupper(substr(PHP_OS, 0, 3) === 'WIN');
    }

    /**
     * Simple popen wrapper.
     * --
     * @param string $command
     * @param string $mode
     * --
     * @return string
     */
    static function popen($command, $mode='r')
    {
        $fp = popen($command, $mode);
        $result = stream_get_contents($fp);
        pclose($fp);

        return $result;
    }

    /**
     * Execute a command.
     * --
     * @param string $command
     * @param array  $params
     * --
     * @return string
     */
    static function execute($command, array $params=[])
    {
        return exec(vsprintf($command, $params));
    }

    /**
     * Fork a command, and stop execution in child.
     * Use 'pcntl_wait($status);' in parent, to prevent zombie processes!
     * --
     * @param  mixed   $commands string|array
     * @param  boolean $print    Result of script execution (use system
     *                           rather than exec).
     * --
     * @return integer pid
     */
    static function fork_command($commands, $print=true)
    {
        $pid = pcntl_fork();

        if ($pid === -1)
        {
            output::format("<red>Cannot fork the process...</red>\n");
            exit(1);
        }
        elseif ($pid === 0)
        {
            // We are the child, execute command and quit!
            if (!is_array($commands))
            {
                $commands = [$commands];
            }

            foreach ($commands as $command)
            {
                if ($print)
                {
                    system($command);
                }
                else
                {
                    exec($command);
                }
            }

            exit(0);
        }
        else
        {
            return $pid;
        }
    }
}

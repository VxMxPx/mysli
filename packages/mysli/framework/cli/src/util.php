<?php

namespace mysli\framework\cli;

__use(__namespace__, '
    ./output AS cout
');

class util {
    /**
     * Detect terminal width.
     * @return integer
     */
    static function terminal_width() {
        $r = 80;
        // standard method
        $r = (int) self::execute('tput cols 2> /dev/null')
            ?: (int) self::execute('echo $COLUMNS 2> /dev/null');
        // try to get it from stty
        if (!$r) {
            $r = self::execute('stty size 2> /dev/null');
            $r = explode(' ', $r, 2);
            $r = (int) (isset($r[1]) ? $r[1] : 0);
        }
        // windows environment
        if (!$r && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $r = self::execute('mode');
            $r = (int) preg_match('/^ *Columns\: *([0-9]+)$/', $r);
        }
        return $r;
    }
    /**
     * Execute command.
     * @param  string $command
     * @param  array  $params
     * @return string
     */
    static function execute($command, array $params = []) {
        return exec(vsprintf($command, $params));
    }
    /**
     * Fork execute command, and stop execution in child.
     * Use 'pcntl_wait($status);' in parent, to prevent zombie processes!
     * @param  mixed   $commands string|array
     * @param  boolean $print result of script execution (use system
     * rather than exec).
     * @return integer pid
     */
    static function fork_command($commands, $print=true) {
        $pid = pcntl_fork();
        if ($pid === -1) {
            cout::format('+red Cannot fork the process...');
            exit(1);
        } else if ($pid === 0) {
            // We are the child, execute command and quit!
            if (!is_array($commands)) $commands = [$commands];
            foreach ($commands as $command) {
                if ($print) {
                    system($command);
                } else {
                    exec($command);
                }
            }
            exit(1);
        } else {
            return $pid;
        }
    }
}

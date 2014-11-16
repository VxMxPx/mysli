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

        if (!is_cli()) {
            return 80;
        } elseif (!self::is_win()) {
            return self::execute('tput cols');
            // $result = self::popen('resize');
            // preg_match("/COLUMNS=([0-9]+)/", $result, $matches);
            // return $matches[1];
        } else {
            $result = self::popen('mode');
            preg_match('/^ *Columns\: *([0-9]+)$/m', $result, $matches);
            return $matches[1];
        }
    }
    /**
     * Check if we're in windows environment
     * @return boolean
     */
    static function is_win() {
        return strtoupper(substr(PHP_OS, 0, 3) === 'WIN');
    }
    /**
     * Simple popen wrapper.
     * @param  string $command
     * @param  string $mode
     * @return string
     */
    static function popen($command, $mode='r') {
        $fp = popen($command, $mode);
        $result = stream_get_contents($fp);
        pclose($fp);

        return $result;
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

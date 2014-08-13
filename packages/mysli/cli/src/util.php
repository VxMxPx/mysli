<?php

namespace mysli\cli {

    use \mysli\type\str as str;
    use \mysli\cli\output as cout;

    class util {
        /**
         * Execute command.
         * @param  string $command
         * @param  array  $params
         * @return string
         */
        static function execute($command, array $params = []) {
            return exec(str::sprint($command, $params));
        }
        /**
         * Fork execute command, and stop execution in child.
         * Use 'pcntl_wait($status);' in parent, to prevent zombie processes!
         * @param  mixed   $commands string|array
         * @param  boolean $print result of script execution use system
         * rather than exec.
         * @return integer pid
         */
        static function fork_command($commands, $print=true) {
            $pid = pcntl_fork();
            if ($pid === -1) {
                cout::error('Cannot fork the process...');
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
}

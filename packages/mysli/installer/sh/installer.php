<?php

namespace mysli\installer\sh\installer;

use mysli\installer\common as c;

/**
 * Execute.
 */
function __init() {
    // Get parameters...
    $short = 'p:d:r:hy';
    $long  = ['pkgpath:', 'datpath:', 'rewrite:', 'help'];
    $options = getopt($short, $long);

    // Do we have request for help?
    if (get_parameter($options, 'h', 'help', 'not') !== 'not') {
        print(intro());
        exit(0);
    }

    $packages = [
        'core'   => 'mysli/framework/core',
        'cli'    => 'mysli/framework/cli',
        'pkgm'   => 'mysli/framework/pkgm',
    ];
    $is_yes  = !get_parameter($options, 'y', false, true); // Need to invert it
    $pkgpath =  get_parameter($options, 'p', 'pkgpath', '<a:packages>');
    $datpath =  get_parameter($options, 'd', 'datpath', '{pkgpath}/../private');
    $rewrite =  get_parameter($options, 'r', 'rewrite', '');
    $rewrite =  explode(',', $rewrite);
    foreach ($rewrite as $rw_item) {
        $rw_item = explode(':', $rw_item);
        if (!isset($rw_item[1])) continue;
        $role = trim($rw_item[0]);
        $pac  = trim($rw_item[1]);
        if (!isset($packages[$role])) continue;
        $packages[$role] = $pac;
    }
    // Absolute full path is needed;
    // If relative path was provided, it needs to be resolved.
    if (substr($pkgpath, 0, 3) === '<a:') {
        $pkgpath = c\discover_path(__DIR__, substr($pkgpath, 3, -1));
        if (!$pkgpath) {
            fatal('Packages path is invalid.');
        }
    } else {
        $pkgpath = c\resolve_path($pkgpath, __DIR__.DIRECTORY_SEPARATOR);
        if ($pkgpath[1]) {
            fatal('Packages path is invalid: ' . implode('', $pkgpath));
        }
        $pkgpath = rtrim($pkgpath[0], DIRECTORY_SEPARATOR);
    }

    if (substr($datpath, 0, 10) === '{pkgpath}/') {
        $datpath_rel = $pkgpath;
        $datpath = substr($datpath, 10);
    } else {
        $datpath_rel = __DIR__;
    }
    $datpath = c\resolve_path($datpath, $datpath_rel.DIRECTORY_SEPARATOR);
    $datpath = $datpath[1] ? implode('', $datpath) : $datpath[0];

    // Ask if all seems ok...
    print_line(null);
    print_line('* Review of the installation.');
    print_line('    Use -h for help.');
    print_line(null);
    print_line('* Paths:');
    print_line('    Packages ' . $pkgpath);
    print_line('    Private  ' . $datpath);
    print_line(null);
    print_line('* List of packages to enable:');
    print_line(nice_array($packages, 4));

    if (!$is_yes) {
        fwrite(STDOUT, '[?] Proceed? [Y/n] ');
        $answer = fread(STDIN, 1);
        if (!in_array(strtolower(trim($answer)), ['y', '']))
            fatal('You selected `no`! See you latter....');
    }

    // Validate data...
    if (!file_exists($pkgpath)) {
        fatal('Cannot continue, packages path is not valid: ' . $pkgpath);
    }

    $missing = [];
    foreach ($packages as $role => $pac)
        if (!file_exists(c\dst($pkgpath, $pac)))
            $missing[$pac] = c\dst($pkgpath, $pac);

    if (!empty($missing))
        fatal("Packages not found:\n" . nice_array($missing, 4));

    // Enable core package...
    print_line(null);
    print_line('* Now enabling core packages....');
    if (c\exe_setup(
        $packages['core'], $pkgpath, $datpath,
        '\mysli\installer\sh\installer\fatal'))
    {
        print_line("    Done: {$packages['core']} (SETUP)");
    }
    $core = c\pkg_class(
        $packages['core'], '__init', $pkgpath,
        '\mysli\installer\sh\installer\fatal');
    $core($datpath, $pkgpath);

    // Enable pkgm package...
    if (c\exe_setup(
        $packages['pkgm'], $pkgpath, $datpath,
        '\mysli\installer\sh\installer\fatal'))
    {
        print_line("    Done: {$packages['pkgm']} (SETUP)");
    }
    $pkgm = c\pkg_class(
        $packages['pkgm'], 'pkgm', $pkgpath,
        '\mysli\installer\sh\installer\fatal');

    if (!$pkgm::enable($packages['core'], 'installer')) {
        fatal("Failed: {$packages['core']}");
    } else {
        print_line("    Done: {$packages['core']}");
    }

    // Enable cli package...
    if (c\exe_setup(
        $packages['cli'], $pkgpath, $datpath,
        '\mysli\installer\sh\installer\fatal'))
    {
        print_line("    Done: {$packages['cli']} (SETUP)");
    }
    if (!$pkgm::enable($packages['cli'], 'installer')) {
        fatal("Failed to enable: {$packages['cli']}");
    } else {
        print_line("    Done: {$packages['cli']}");
    }

    print_line('    All done!');
    include(realpath(c\dst($pkgpath, $packages['pkgm'], '/sh/pkgm.php')));
    call_user_func(substr($pkgm, 0, strrpos($pkgm, '\\')).'\\sh\\pkgm\\repair');
}

/**
* Get intro help message.
* @return string
*/
function intro() {
    return <<<EOI

Mysli Installer

Usage: php mysli.installer.phar [OPTIONS]...

  -p, --pkgpath <name>    Packages\'s path. The default is: <a:packages>
                          This will try to automatically discover packages path.
                          If you want to enter costume path, you can enter a
                          relative path (../../packages) or a full absolute path.
  -d, --datpath <name>    Data / private path (where configuration and databases
                          will be stored. Should not be URL accessible)
                          The default is: {pkgpath}/../private
  -r, --replace <options> Replace core packages in format: role:vendor\package,...
                          The default values are:
                          core:mysli/framework/core,cli:mysli/framework/cli,
                          pkgm:mysli/framework/pkgm

  -y                      Assume `yes` as an answer to all questions.
  -h, --help              Print this help.

EOI;
}
/**
 * Print a line.
 * @param  string $line
 */
function print_line($line) {
    fwrite(STDOUT, $line . PHP_EOL);
}
/**
 * Print line and exit(1)
 * @param  string $line
 */
function fatal($line) {
    print_line('[!] '.$line);
    exit(1);
}
/**
 * Get parameter by short or long name, or default if not found.
 * @param  array  $data
 * @param  string $short
 * @param  string $long
 * @param  mixed  $default
 * @return mixed
 */
function get_parameter(array $data, $short, $long, $default) {
    if (!is_array($data))               return $default;
    if ($short && isset($data[$short])) return $data[$short];
    if ($long  && isset($data[$long]))  return $data[$long];
    return $default;
}
/**
 * Formay an array for CLI output.
 * @param  array   $input
 * @param  integer $indent
 * @return string
 */
function nice_array(array $input, $indent=0) {
    $lkey = 0;
    $out  = '';
    // Get the longes key...
    foreach ($input as $key => $val) {
        if (strlen($key) > $lkey) {
            $lkey = strlen($key);
        }
    }
    foreach ($input as $key => $value) {
        $out .=
            str_repeat(' ', $indent) .
            $key . str_repeat(' ', $lkey - strlen($key)) .
            ' : ' . $value . "\n";
    }
    return $out;
}
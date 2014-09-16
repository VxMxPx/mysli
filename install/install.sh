#!/usr/bin/env php
<?php

define('MYSLI_INSTALLER_VERSION', '1.1.6');

/**
* Print help message.
* @return null
*/
function print_intro() {
    $intro =
        "Mysli Installer (v" . MYSLI_INSTALLER_VERSION . ")\n".
        "\n".
        "Usage: ./installer [OPTIONS]...\n".
        "\n".
        "  -p, --pkgpath <name>   Packages\'s path. The default is: ../packages\n".
        "  -d, --datpath <name>   Data / private path (where configuration and databases\n".
        "                         will be stored. Should not be URL accessible).\n".
        "                         The default is: ../private\n".
        "  -r, -rewrite <options> Rewrite core packages, in format: role:vendor\package,...\n".
        "                         The default values are:\n".
        "                         core:mysli/framework/core,cli:mysli/framework/cli,pkgm:mysli/framework/pkgm\n".
        "\n".
        "  -y                     Assume `yes` as an answer to all questions.\n".
        "  -h, --help             Print this help.\n";
    fwrite(STDOUT, $intro);
}
/**
 * Print line.
 * @param  string $line
 * @return null
 */
function print_line($line) {
    fwrite(STDOUT, $line . PHP_EOL);
}
/**
 * Print line and exit(1)
 * @param  string $line
 * @return null
 */
function print_line_and_die($line) {
    print_line('!!ERROR: ' . $line);
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
 * @param  array  $input
 * @return string
 */
function nice_array(array $input) {
    $lkey = 0;
    $out  = '';
    // Get the longes key...
    foreach ($input as $key => $val) {
        if (strlen($key) > $lkey) {
            $lkey = strlen($key);
        }
    }
    foreach ($input as $key => $value) {
        $out .= $key . str_repeat(' ', $lkey - strlen($key)) . ' : ' . $value . "\n";
    }
    return $out;
}

// Start installer

// Check if we can find common.php
if (!file_exists(__DIR__ . '/common.php')) {
    print_line_and_die('Cannot find common.php in: ' . __DIR__);
}
include __DIR__ . '/common.php';

// // Get parameters...
$short = 'p:d:r:hy';
$long  = ['pkgpath:', 'datpath:', 'rewrite:', 'help'];
$options = getopt($short, $long);

// // Do we have help request?
if (get_parameter($options, 'h', 'help', 'not') !== 'not') {
    print_intro();
    exit(0);
}

$packages = [
    'core'   => 'mysli/framework/core',
    'cli'    => 'mysli/framework/cli',
    'pkgm'   => 'mysli/framework/pkgm',
];
$is_yes  = !get_parameter($options, 'y', false, true); // Need to invert it
$pkgpath = get_parameter($options, 'p', 'pkgpath', '../packages');
$datpath = get_parameter($options, 'd', 'datpath', '../private');
$rewrite = get_parameter($options, 'r', 'rewrite', '');
$rewrite = explode(',', $rewrite);
foreach ($rewrite as $rw_item) {
    $rw_item = explode(':', $rw_item);
    if (!isset($rw_item[1])) continue;
    $role = trim($rw_item[0]);
    $pac  = trim($rw_item[1]);
    if (!isset($packages[$role])) continue;
    $packages[$role] = $pac;
}

// Absolute full path is needed; if relative path was provided,
// it needs to be resolved.

// Resolve paths
$pkgpath = resolve_path($pkgpath, __DIR__ . DIRECTORY_SEPARATOR);
if ($pkgpath[1]) {
    print_line_and_die('Packages path is invalid: ' . implode('', $pkgpath));
}
$pkgpath = rtrim($pkgpath[0], DIRECTORY_SEPARATOR);

$datpath = resolve_path($datpath, __DIR__ . DIRECTORY_SEPARATOR);
$datpath = $datpath[1] ? implode('', $datpath) : $datpath[0];

// Ask if all seems ok...
print_line('Review before setup:');
print_line(null);
print_line('--- Paths ---');
print_line('Private  ' . $datpath);
print_line('Packages ' . $pkgpath);
print_line(null);
print_line('--- List of packages to enable ---');
print_line(nice_array($packages));

if (!$is_yes) {
    fwrite(STDOUT, 'Proceed? [Y/n] ');
    $answer = fread(STDIN, 1);
    if (!in_array(strtolower(trim($answer)), ['y', '']))
        print_line_and_die('You selected `no`! See you latter....');
}

// Validate data...
if (!file_exists($pkgpath)) {
    print_line_and_die(
        'Cannot continue, packages path is not valid: ' . $pkgpath);
}

$missing = [];
foreach ($packages as $role => $pac)
    if (!file_exists(dst($pkgpath, $pac)))
        $missing[$pac] = dst($pkgpath, $pac);

if (!empty($missing))
    print_line_and_die("Packages not found:\n" . nice_array($missing));

// Enable core package...
if (exe_setup($packages['core'], $pkgpath, $datpath, 'print_line_and_die')) {
    print_line('Core was successfully enabled.');
}
$core = pkg_class($packages['core'], '__init', $pkgpath, 'print_line_and_die');
$core($datpath, $pkgpath);

// Enable pkgm package...
if (exe_setup($packages['pkgm'], $pkgpath, $datpath, 'print_line_and_die')) {
    print_line('Pkgm was successfully enabled.');
}
$pkgm = pkg_class($packages['pkgm'], 'pkgm', $pkgpath, 'print_line_and_die');

if (!$pkgm::enable($packages['core'])) {
    print_line_and_die("Failed to add `core` to enabled packages list.");
} else {
    print_line('Core was successfully added to the enabled packages list.');
}

// Enable cli package...
if (exe_setup($packages['cli'], $pkgpath, $datpath, 'print_line_and_die')) {
    print_line('Pkgm was successfully enabled.');
}
if (!$pkgm::enable($packages['cli'])) {
    print_line_and_die("Failed to add `cli` to enabled packages list.");
} else {
    print_line('Cli was successfully added to the enabled packages list.');
}

print_line("All done! Now please run: `cd {$datpath} && ./dot pkgm --repair`");

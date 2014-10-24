<?php

namespace mysli\util\tplp\script;

__use(__namespace__, '
    ./{util,parser}
    mysli/framework/fs/{fs,file,dir}
    mysli/framework/type/{arr,str}
    mysli/framework/cli/{output,input,param,util} AS {cout,cinput,cparam,cutil}
');

class tplp {

    /**
     * CLI front-end.
     * @param array $arguments
     * @return null
     */
    static function run(array $args) {
        $params = new cparam('Mysli Tplp Builder', $args);
        $params->command = 'tplp';
        $params->description_long = l("* If --source and
            --destination are not provided, they'll be set from `mysli.pkg.ym`
            (tplp section), if not defined there, defaults will be used.");

        $params->add(
            '--watch/-w',
            ['type'    => 'bool',
             'default' => false,
             'help'    => l("Watch the source folder
                            and rebuild templates if changed")]);
        $params->add(
            '--source/-s',
            ['type'    => 'str',
             'default' => 'tplp*',
             'help'    => 'Directory where templates are located']);
        $params->add(
            '--destination/-d',
            ['type'    => 'str',
             'default' => '*',
             'help'    => l("Directory where templates will be saved.
                            By default this is MYSLI_DATPATH/templ/tplp/cache.
                            If you set this value, it will be relative to the
                            root of your package.")]);
        $params->add(
            'PACKAGE',
            ['type'       => 'str',
             'help'       => 'Package name, e.g.: mysli/web/ui',
             'required'   => true]);

        $params->parse();

        if (!$params->is_valid()) {
            cout::line($params->messages());
            return;
        }

        $v = $params->values();
        $package = $v['package'];
        $watch   = $v['watch'];

        // Check weather path was set || was defined in mysli.pkg || default
        list($source, $destination) = util::get_default_paths($package);

        if (substr($v['source'], -1) === '*') {
            if (!$source) {
                $source = substr($v['source'], 0, -1);
            }
        } else {
            $source = $v['source'];
        }
        if (substr($v['destination'], -1) === '*') {
            if (!$destination) {
                $destination = substr($v['destination'], 0, -1);
            }
        } else {
            $destination = $v['destination'];
        }

        return self::observe_or_parse($package, $source, $destination, $watch);
    }
    /**
     * Compare two lists of files, and return changes for each file.
     * @param  array   $one
     * @param  array   $two
     * @param  integer $cutoff how much of path to remove (for pretty reports)
     * @return array
     */
    private static function what_changed(array $one, array $two, $cutoff=0) {
        $changes = [];

        foreach ($one as $file => $hash) {
            if (!arr::key_in($two, $file)) {
                $changes[str::slice($file, $cutoff)] = 'Added';
            } else {
                if ($two[$file] !== $hash) {
                    $changes[str::slice($file, $cutoff)] = 'Updated';
                }
                unset($two[$file]);
            }
        }

        if (!empty($two)) {
            foreach ($two as $file => $hash) {
                $changes[str::slice($file, $cutoff)] = 'Removed';
            }
        }

        return $changes;
    }
    /**
     * Observe (and) build assets.
     * @param  string  $package
     * @param  string  $source  dir
     * @param  string  $dest    dir
     * @param  boolean $loop
     * @return null
     */
    private static function observe_or_parse($package, $source, $dest, $loop) {

        // Check if we have a valid path
        if (!dir::exists($source)) {
            cout::yellow("Source path is invalid: `{$source}`");
            return false;
        }

        // Dest path
        if (!dir::exists($dest)) {
            if (!cinput::confirm(l("Destination directory (`{$dest}`) not found.
                                    Create it now?")))
            {
                cout::line('Terminated.');
                return false;
            } else {
                if (dir::create($dest)) {
                    cout::success("Directory successfully created.");
                } else {
                    cout::error("Failed to create directory.");
                    return false;
                }
            }
        }

        $signature = [];
        $initial = [];

        do {

            $rsignature = file::signature(self::observable_files($source));

            if ($rsignature !== $signature) {

                $changes = self::what_changed(
                            $rsignature, $signature, strlen($source)+1);

                if (!$initial) {
                    $initial = $changes;
                }

                if (!empty($changes)) {

                    // Check if master changed...
                    foreach ($changes as $file => $change) {
                        if (strpos(file::name($file), '_') !== false) {
                            cout::line("Layout file changed: `{$file}`.");
                            $changes = $initial;
                            break;
                        }
                    }

                    $signature = $rsignature;

                    foreach ($changes as $file => $change) {

                        $real_path = fs::ds($source, $file);
                        $real_file = file::name($real_path, true);
                        $real_source = dirname($real_path);

                        $file_padded = strlen($file) > 35
                            ? substr($file, 0, 32) . '...'
                            : str_pad($file, 35);

                        cout::line(
                            str_pad($change, 7)." > {$file_padded} > ",
                            false);

                        if ($change === 'Removed') {
                            cout::format("+right+green OK");
                            continue;
                        }

                        $destination_file = fs::ds(
                                                $dest,
                                                substr($file, 0, -4).'php');

                        cout::line("Parsing > ", false);
                        try {
                            $parsed = parser::file($real_file, $real_source);
                            cout::line("Writting > ", false);
                            file::create_recursive($destination_file, true);
                            file::write($destination_file, $parsed);
                        } catch (\Exception $e) {
                            cout::format("+right+red FAILED");
                            cout::line($e->getMessage());
                            continue;
                        }
                        cout::format("+right+green OK");
                    }

                }
            }

            $loop and sleep(3);
        } while ($loop);
    }
    /**
     * Get list of files to observe
     * @param  string $dir
     * @return array
     */
    private static function observable_files($dir) {
        $observable = [];
        $files = fs::ls($dir);

        foreach ($files as $file) {
            $fpath = fs::ds($dir, $file);
            if (dir::exists($fpath)) {
                $observable = array_merge(
                                $observable, self::observable_files($fpath));
                continue;
            }
            // if (substr($file, 0, 1) === '_') {
            //     continue;
            // }
            if (substr($file, -5) !== '.tplp') {
                continue;
            }
            $observable[] = $fpath;
        }

        return $observable;
    }
}

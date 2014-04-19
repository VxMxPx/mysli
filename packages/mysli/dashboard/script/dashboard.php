<?php

namespace Mysli\Dashboard\Script;

class Dashboard
{
    private $web;
    private $assets;

    public function __construct($web)
    {
        $this->web = $web;
        $this->assets = \Core\JSON::decode_file(pkgpath('mysli/dashboard/assets.json'), true);
    }

    /**
     * Print general help.
     */
    public function help_index()
    {
        \Cli\Util::doc(
            'Mysli Dashboard :: Buld Script',
            'dashboard <OPTION>',
            [
                'observe templates,i18n,assets|all' =>
                    'Observe: templates, i18n and assets; when changed: parse, merge, compress and publish.',
            ]
        );

        return true;
    }

    public function action_observe($args = 'all')
    {
        $what = \Core\Str::explode_trim(',', $args);

        if (in_array('templates', $what) || in_array('all', $what)) {
            \Cli\Util::fork_command(datpath('dot') . ' tplp observe mysli/dashboard');
        }

        if (in_array('i18n', $what) || in_array('all', $what)) {
            \Cli\Util::fork_command(datpath('dot') . ' i18n observe mysli/dashboard');
        }

        if (in_array('assets', $what) || in_array('all', $what)) {
            \Cli\Util::fork_command(datpath('dot') . ' dashboard assets');
        }

        pcntl_wait($status);
    }

    protected function what_changed($one, $two) {
        $changes = [];
        foreach ($one as $file => $hash) {
            if (!isset($two[$file])) {
                $changes[] = 'Added: ' . $file;
            } else {
                if ($two[$file] !== $hash) {
                    $changes[] = 'Updated: ' . $file;
                }
                unset($two[$file]);
            }
        }
        if (!empty($two)) {
            foreach ($two as $file => $hash) {
                $changes[] = 'Removed: ' . $file;
            }
        }
        return $changes;
    }

    public function action_assets()
    {
        $signature = null;
        $path = pkgpath('mysli/dashboard/assets/');

        while (true) {
            sleep(2);
            $rsignature = \Core\FS::dir_signatures(ds($path, 'src'));
            if ($rsignature === $signature) { continue; }
            \Cli\Util::plain("What changed: \n" . \Core\Arr::readable(
                $this->what_changed($rsignature, $signature)
            ));
            \Cli\Util::plain('Rebuilding assets...');
            $signature = $rsignature;
            $this->assets_merge();
            \Core\FS::dir_copy(ds($path), $this->web->path('mysli/dashboard'), \Core\FS::EXISTS_REPLACE);
        }
    }

    protected function assets_merge()
    {
        $contents = [];
        foreach ($this->assets as $file => $assets) {
            foreach ($assets as $asset) {
                $filename = pkgpath('mysli/dashboard/assets/src/', $asset);
                if (!file_exists($filename)) {
                    \Cli\Util::warn('File not found: ' . $filename);
                    continue;
                }
                // if (substr($asset, -5) === '.styl') {
                //     $out = [];
                //     exec('cat ' . $filename . ' | stylus -c', $out);
                //     $content = implode('', $out);
                // } else {
                    $content = file_get_contents($filename);
                // }
                $contents[$file] = isset($contents[$file]) ? $contents[$file] . $content : $content;
            }
        }
        foreach ($contents as $file => $content) {
            $filename = pkgpath('mysli/dashboard/assets/dist', $file);
            if (file_put_contents($filename, $content) !== false) {
                \Cli\Util::success('Merged: ' . $file);
                if (substr($file, -3) === '.js') {
                    // Rewrite itself
                    system('uglifyjs -c -o ' . $filename . ' ' . $filename);
                } else {
                    $out = [];
                    exec('cat ' . $filename . ' | stylus -c', $out);
                    file_put_contents($filename, implode('', $out));
                }
            } else {
                \Cli\Util::error('Failed to create: ' . $file);
            }
        }
    }
}

<?php

namespace mysli\assets\root\script; class assets
{
    const __use = '
        .{ map }
        mysli.toolkit.cli.{ prog, param, ui }
        mysli.toolkit.fs.{ fs, file, dir }
    ';


    /**
     * Run Assets CLI.
     * --
     * @param array $args
     * --
     * @return boolean
     */
    static function __run(array $args)
    {
        /*
        Set params.
         */
        $prog = new prog('Mysli Assets', 'mysli.assets.assets');

        $prog->set_help(true);
        $prog->set_version('mysli.assets', true);

        $prog
        ->create_parameter('PACKAGE', [
            'required' => true,
            'help'     => 'Package\'s assets to be processed, in format: '.
                        '`vendor.package`. '.
                        'Alternatively relative path to the assets map file '.
                        'can be used. Use `./` for current directory.'
        ])
        ->create_parameter('--watch/-w', [
            'type' => 'boolean',
            'def'  => false,
            'help' => 'Watch directory and re-parse when changes occurs.'
        ])
        ->create_parameter('--debug/-d', [
            'type' => 'boolean',
            'def'  => true,
            'help' => 'This will not compress nor merge assets, resulting in faster processing.'
        ])
        ->create_parameter('--publish/-p', [
            'type' => 'boolean',
            'help' => 'Copy files to public directory.'
        ])
        ->create_parameter('--file/-f', [
            'help' => 'Observe only specific file (defined in map.ym).'
        ]);

        if (null !== ($r = prog::validate_and_print($prog, $args)))
            return $r;

        list($package, $publish, $file, $watch) =
            $prog->get_values('package', '-p', '-f', '-w');

        // Resolve provided path, return array map and string path (absolute).
        list( $map, $path ) = static::resolve($package);

        if (!dir::exists($path))
        {
            ui::warn("Couldn't resolve: `{$package}`.");
            return false;
        }

        if (!$map)
        {
            ui::warn("Couldn't find `map.ym` for: `{$package}`.");
            return false;
        }

        if (!isset($map['id']))
        {
            ui::warn(
                "Couldn't find `id`, make sure to define it ".
                "in your `map.ym` file."
            );
            return false;
        }

        return static::process($map, $path, $publish, $file, $watch);
    }


    /*
    --- Protected --------------------------------------------------------------
     */

    /**
     * Parse templates in particular path, and watch for change.
     * --
     * @param string  $path
     * @param boolean $static
     * @param boolean $path
     * --
     * @return boolean
     */
    protected static function parse($path, $static, $watch)
    {

    }

    /**
     * Get map for package / path.
     * --
     * @param string $id
     * --
     * @return array
     *         [ array $map, string $path ]
     */
    protected static function resolve($id)
    {
        // Package
        if (preg_match('/^[a-z0-9\.]$/', $id))
        {
            $path = pkg::get_path($id);
            $map = map::by_package($id);
        }
        // Or relative path
        else
        {
            $path = realpath(getcwd()."/{$id}");
            $map = map::by_path(fs::ds($path, 'map.ym'));
        }

        return [ $map, $path ];
    }
}

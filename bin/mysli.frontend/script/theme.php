<?php

namespace mysli\frontend\root\script; class theme
{
    const __use = '
        .{ theme -> lib.theme }
        mysli.toolkit.{ pkg }
        mysli.toolkit.cli.{ prog, ui }
    ';

    /**
     * Run theme cli.
     * --
     * @param array $args
     */
    static function __run(array $args)
    {
        /*
        Set parameters.
         */
        $prog = new prog('Mysli Frontend Theme', __CLASS__);

        $prog->set_help(true);
        $prog->set_version('mysli.frontend', true);
        $prog->set_description('Manage Themes.');

        $prog
        ->create_parameter('-a/--activate', [
            'help' => 'Activate particular theme.',
        ])
        ->create_parameter('--list', [
            'type'    => 'boolean',
            'exclude' => $prog->get_parameters('activate'),
            'help'    => 'Display a list of themes. Available options.',
        ])
        ->create_parameter('--meta', [
            'exclude' => $prog->get_parameters('activate', 'list'),
            'help'    => 'Display meta information for particular theme.'
        ]);

        if (null !== ($r = prog::validate_and_print($prog, $args)))
            return $r;

        $value = $prog->get_option_at(0, 'value');

        switch ($prog->get_option_at(0, 'name'))
        {
            case 'activate':
                return static::activate($value);

            case 'list':
                return static::do_list();

            case 'meta':
                return static::meta($value);

            default:
                ui::warning(
                    'WARNING',
                    'Invalid command, use --help to see available commands.');
                return false;
        }
    }

    /*
    --- Protected --------------------------------------------------------------
     */

    /**
     * Set active theme.
     * --
     * @param string $theme
     * --
     * @return boolean
     */
    protected static function activate($theme)
    {
        try
        {
            if (!lib\theme::set_active($theme))
            {
                ui::error("FAILED", "Couldn't activate theme: `{$theme}`");
                return false;
            }
            else
            {
                ui::success('OK', "Theme was activated: `{$theme}`");
                return true;
            }
        }
        catch (\Exception $e)
        {
            ui::error("FAILED", $e->getMessage());
            return false;
        }
    }

    /**
     * Print theme's meta.
     * --
     * @param string $theme
     * --
     * @return boolean
     */
    protected static function meta($theme)
    {
        $meta = pkg::get_meta($theme);

        ui::title("Meta for {$theme}");
        ui::list($meta, ui::list_aligned);

        return true;
    }

    /**
     * Print list of themes.
     * --
     * @return boolean
     */
    protected static function do_list()
    {
        $active = lib\theme::get_active();
        $all    = lib\theme::get_list();
        $list   = [];

        foreach ($all as $id => $meta)
        {
            if ($active === $id)
                $list[$id] = ui::strong('[*] '.$meta['frontend']['name']);
            else
                $list[$id] = $meta['frontend']['name'];
        }

        ui::title('List of themes');
        ui::list($list, ui::list_aligned);

        return true;
    }

}

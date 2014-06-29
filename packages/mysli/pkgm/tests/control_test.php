<?php

namespace Mysli\Pkgm;

include(__DIR__.'/_common.php');

Generator::drop_packages();
Generator::generate_packages();

class ControlTest extends \PHPUnit_Framework_TestCase
{
    private $registry;

    public function __construct()
    {
        \Core\FS::dir_create(datpath('pkgm'));
        file_put_contents(datpath('pkgm/registry.json'), json_encode(['enabled' => []]));
        $this->registry = new Registry(datpath('pkgm/registry.json'));
    }

    private function get_instance($package)
    {
        return new Control($package, $this->registry);
    }

    private function get_reg_meta($package)
    {
        $meta = json_decode(file_get_contents(datpath('pkgm/registry.json')), true);
        return $meta['enabled'][$package];
    }

    // enable ------------------------------------------------------------------

    public function test_enable()
    {
        $this->get_instance('mysliio/core')->enable();

        $meta = $this->get_reg_meta('mysliio/core');

        $this->assertEquals(
            'mysliio/core',
            $meta['name']
        );
    }

    // public function test_enable_by_role()
    // {
    //     $this->get_instance('@core')->enable();

    //     $meta = $this->get_reg_meta('mysliio/core');

    //     $this->assertEquals(
    //         'mysliio/core',
    //         $meta['package']
    //     );
    // }

    public function test_enable_required_by_add()
    {
        $this->get_instance('mysliio/core')->enable();
        $this->get_instance('mysliio/pkgm')->enable();

        $meta = $this->get_reg_meta('mysliio/core');

        $this->assertEquals(
            [
                'mysliio/pkgm'
            ],
            $meta['required_by']
        );
    }

    /**
     * @expectedException \Core\ValueException
     */
    public function test_enable_exception()
    {
        $c = $this->get_instance('mysliio/core');
        $c->enable();
        $c->enable();
    }

    // disable -----------------------------------------------------------------

    public function test_disable_required_by_removed()
    {
        $this->get_instance('mysliio/core')->enable();
        $c = $this->get_instance('mysliio/pkgm');
        $c->enable(); $c->disable();

        $meta = $this->get_reg_meta('mysliio/core');

        $this->assertEquals(
            [],
            $meta['required_by']
        );
    }

    /**
     * @expectedException \Core\ValueException
     */
    public function test_disable_exception()
    {
        $c = $this->get_instance('mysliio/core');
        $c->disable();
    }

    // process_factory_entry ---------------------------------------------------

    // public function test_process_factory_entry_null()
    // {
    //     $this->assertEquals(
    //         [
    //             'instantiation' => 'null',
    //             'inject'        => []
    //         ],
    //         Control::process_factory_entry('null()')
    //     );
    // }

    // public function test_process_factory_entry_name()
    // {
    //     $this->assertEquals(
    //         [
    //             'instantiation' => 'name',
    //             'inject'        => []
    //         ],
    //         Control::process_factory_entry('name()')
    //     );
    // }

    // public function test_process_factory_entry_construct()
    // {
    //     $this->assertEquals(
    //         [
    //             'instantiation' => 'construct',
    //             'inject'        => ['mysliio/core', 'mysliio/pkgm']
    //         ],
    //         Control::process_factory_entry('construct(mysliio/core, mysliio/pkgm)')
    //     );
    // }

    // public function test_process_factory_entry_singleton()
    // {
    //     $this->assertEquals(
    //         [
    //             'instantiation' => 'singleton',
    //             'inject'        => ['mysliio/core']
    //         ],
    //         Control::process_factory_entry('singleton(mysliio/core)')
    //     );
    // }
}

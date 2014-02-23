<?php

namespace Mysli;

include(__DIR__.'/../pkgm.php');    // Include self
include(__DIR__.'/../../core/core.php'); // CORE is needed!
new \Mysli\Core(
    realpath(__DIR__.'/dummy/private'),
    realpath(__DIR__.'/dummy/packages')
);

class PkgmTest extends \PHPUnit_Framework_TestCase
{
    protected $pkgm;

    public function __construct()
    {
        // Make sure we're working with fresh copy...
        $this->pkgm = $this->reset_packages();
    }

    protected function reset_packages()
    {
        file_put_contents(datpath('pkgm/registry.json'), '{}');
        $pkgm = new Pkgm();
        $pkgm->enable('ns1/pac1');
        $pkgm->enable('ns1/pac2');
        $pkgm->enable('ns2/paca');
        $pkgm->enable('ns3/pac3a');
        return $pkgm;
    }

    public function test_get_enabled()
    {
        $packages = $this->pkgm->get_enabled();
        $this->assertCount(4, $packages);
    }

    public function test_get_enabled_detailed()
    {
        $this->assertEquals(
            json_decode(file_get_contents(datpath('/pkgm/registry.json')), true),
            $this->pkgm->get_enabled(true)
        );
    }

    public function test_get_details_enabled()
    {
        $this->assertEquals(
            'ns1/pac1',
            $this->pkgm->get_details('ns1/pac1')['package']
        );
    }

    public function test_get_disabled()
    {
        $disabled = $this->pkgm->get_disabled();
        $this->assertTrue(is_array($disabled));
        $this->assertTrue(!empty($disabled));
        $this->assertTrue(in_array('ns2/pacb', $disabled));
    }

    public function test_get_disabled_detailed()
    {
        $disabled = $this->pkgm->get_disabled(true);

        $this->assertEquals(
            'ns2/pacb',
            $disabled['ns2/pacb']['package']
        );
    }

    public function test_get_details_disabled()
    {
        $this->assertEquals(
            'ns2/pacb',
            $this->pkgm->get_details('ns2/pacb')['package']
        );
    }

    /**
     * @expectedException Mysli\Core\NotFoundException
     */
    public function test_get_details_nonexisting()
    {
        $this->pkgm->get_details('vendor/package');
    }

    public function test_resolve_normal()
    {
        $this->assertEquals(
            'ns1/pac1',
            $this->pkgm->resolve('ns1/pac1')
        );
    }

    public function test_resolve_disabled_normal()
    {
        $this->assertEquals(
            'ns2/pacb',
            $this->pkgm->resolve('ns2/pacb')
        );
    }

    public function test_resolve_regex()
    {
        $this->assertEquals(
            'ns1/pac1',
            $this->pkgm->resolve('*/pac1')
        );
    }

    public function test_resolve_name_failed()
    {
        $this->assertFalse(
            $this->pkgm->resolve('vendor/package')
        );
    }

    // public function test_resolve_all()
    // {
    //     $this->assertEquals(
    //         [
    //             'mysli/users',
    //             'avrelia/users'
    //         ],
    //         $this->pkgm->resolve_all('*/users')
    //     );
    // }

    // public function test_resolve_all_one_match()
    // {
    //     $this->assertEquals(
    //         [
    //             'avrelia/backend'
    //         ],
    //         $this->pkgm->resolve_all('avrelia/backend')
    //     );
    // }

    // public function test_resolve_all_no_match()
    // {
    //     $this->assertEquals(
    //         [],
    //         $this->pkgm->resolve_all('*/non_existant')
    //     );
    // }

    public function test_factory()
    {
        $object = $this->pkgm->factory('ns1/pac1');
        $this->assertInstanceOf('Ns1\\Pac1', $object);
    }

    public function test_dependencies_factory()
    {
        $details = $this->pkgm->get_details('ns2/paca');
        $dependencies = $this->pkgm->dependencies_factory($details['depends_on']);
        $this->assertInstanceOf('Ns1\\Pac2', $dependencies['pac2']);
    }

    public function test_construct_setup()
    {
        $setup = $this->pkgm->construct_setup('ns1/pac1');
        $this->assertInstanceOf('Ns1\\Pac1\\Setup', $setup);
    }

    public function test_construct_setup_no_file()
    {
        $setup = $this->pkgm->construct_setup('ns1/pac2');
        $this->assertFalse($setup);
    }

    public function test_autoloader()
    {
        $object = new \Ns2\Paca();
        $this->assertInstanceOf('Ns2\\Paca', $object);
    }

    public function test_autoloader_subclass()
    {
        $obj = new \Ns2\Paca\PacaSubclass();
        $this->assertInstanceOf('Ns2\\Paca\\PacaSubclass', $obj);
    }

    public function test_autoloader_subclass_exception()
    {
        $exception = new \Ns2\Paca\BaseException();
        $this->assertInstanceOf('Ns2\\Paca\\BaseException', $exception);
    }

    public function test_load_existing()
    {
        $this->assertTrue($this->pkgm->load('ns1/pac1'));
    }

    public function test_load_non_existing()
    {
        $this->assertFalse($this->pkgm->load('vendor/package'));
    }

    public function test_call()
    {
        $this->assertEquals(
            'hi',
            $this->pkgm->call('ns1/pac1', 'say_hi')
        );
    }

    public function test_call_params()
    {
        $random_numer = rand(0, 100);
        $this->assertEquals(
            'The random number is: ' . $random_numer,
            $this->pkgm->call('ns1/pac1', 'say_number', [$random_numer])
        );
    }

    public function test_is_enabled()
    {
        $this->assertTrue(
            $this->pkgm->is_enabled('ns1/pac1')
        );
        $this->assertFalse(
            $this->pkgm->is_enabled('ns2/pacb')
        );
        $this->assertFalse(
            // Non-existant
            $this->pkgm->is_enabled('vendor/package')
        );
    }

    public function test_get_dependencies_simple()
    {
        $this->assertEquals(
            [
                'enabled' => [
                    'ns1/pac2' => '>= 1'
                ],
                'disabled' => [],
                'missing'  => []
            ],
            $this->pkgm->get_dependencies('ns2/paca')
        );
    }

    public function test_get_dependencies_missing()
    {
        $this->assertEquals(
            [
                'enabled'  => [
                    'ns1/pac1' => '>= 1',
                    'ns2/paca' => '>= 0'
                ],
                'disabled' => [],
                'missing'  => [
                    '*/pacc' => '>= 4'
                ]
            ],
            $this->pkgm->get_dependencies('ns2/pacb')
        );
    }

    // Deep scan only for disabled packages!
    // public function test_get_dependencies_deep()
    // {
    //     dump($this->pkgm->get_dependencies('ns2/paca', true));
    //     $this->assertEquals(
    //         [
    //             'enabled' => [
    //                 'ns1/pac1' => '>= 1',
    //                 'ns1/pac2' => '>= 1'
    //             ],
    //             'disabled' => [],
    //             'missing'  => []
    //         ],
    //         $this->pkgm->get_dependencies('ns2/paca', true)
    //     );
    // }

    public function test_get_dependees()
    {
        $this->assertEquals(
            ['ns1/pac2', 'ns3/pac3a'],
            $this->pkgm->get_dependees('ns1/pac1')
        );
    }

    // public function test_get_dependees_deep()
    // {
    //     $this->assertEquals(
    //         ['mysli/backend', 'mysli/session'],
    //         $this->pkgm->get_dependees('mysli/users', true)
    //     );
    // }

    // public function test_dump_final()
    // {
    //     $this->pkgm->factory('ns3/pac3a');
    // }
}

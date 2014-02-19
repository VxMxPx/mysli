<?php

namespace Mysli;

include(__DIR__.'/../librarian.php');    // Include self
include(__DIR__.'/../../core/core.php'); // CORE is needed!
new \Mysli\Core(
    realpath(__DIR__.'/dummy/private'),
    realpath(__DIR__.'/dummy/libraries')
);

class LibrarianTest extends \PHPUnit_Framework_TestCase
{
    protected $librarian;

    public function __construct()
    {
        // Make sure we're working with fresh copy...
        $this->librarian = $this->reset_libraries();
    }

    protected function reset_libraries()
    {
        file_put_contents(
            datpath('librarian/registry.json'),
            // Create dummy reference to itself so that
            // librarian exceptions autoloader will function correctly.
            '{"mysli/librarian" : {"library" : "mysli/librarian"}}'
        );
        $librarian = new Librarian();
        $librarian->enable('ns1/pac1');
        $librarian->enable('ns1/pac2');
        $librarian->enable('ns2/paca');
        return $librarian;
    }

    public function test_get_enabled()
    {
        $libraries = $this->librarian->get_enabled();
        $this->assertCount(4, $libraries);
    }

    public function test_get_enabled_detailed()
    {
        $this->assertEquals(
            json_decode(file_get_contents(datpath('/librarian/registry.json')), true),
            $this->librarian->get_enabled(true)
        );
    }

    public function test_get_details_enabled()
    {
        $this->assertEquals(
            'ns1/pac1',
            $this->librarian->get_details('ns1/pac1')['library']
        );
    }

    public function test_get_disabled()
    {
        $disabled = $this->librarian->get_disabled();
        $this->assertTrue(is_array($disabled));
        $this->assertTrue(!empty($disabled));
        $this->assertTrue(in_array('ns2/pacb', $disabled));
    }

    public function test_get_disabled_detailed()
    {
        $disabled = $this->librarian->get_disabled(true);

        $this->assertEquals(
            'ns2/pacb',
            $disabled['ns2/pacb']['library']
        );
    }

    public function test_get_details_disabled()
    {
        $this->assertEquals(
            'ns2/pacb',
            $this->librarian->get_details('ns2/pacb')['library']
        );
    }

    /**
     * @expectedException Mysli\Core\NotFoundException
     */
    public function test_get_details_nonexisting()
    {
        $this->librarian->get_details('vendor/library');
    }

    public function test_resolve_normal()
    {
        $this->assertEquals(
            'ns1/pac1',
            $this->librarian->resolve('ns1/pac1')
        );
    }

    public function test_resolve_disabled_normal()
    {
        $this->assertEquals(
            'ns2/pacb',
            $this->librarian->resolve('ns2/pacb')
        );
    }

    public function test_resolve_regex()
    {
        $this->assertEquals(
            'ns1/pac1',
            $this->librarian->resolve('*/pac1')
        );
    }

    public function test_resolve_name_failed()
    {
        $this->assertFalse(
            $this->librarian->resolve('vendor/library')
        );
    }

    // public function test_resolve_all()
    // {
    //     $this->assertEquals(
    //         [
    //             'mysli/users',
    //             'avrelia/users'
    //         ],
    //         $this->librarian->resolve_all('*/users')
    //     );
    // }

    // public function test_resolve_all_one_match()
    // {
    //     $this->assertEquals(
    //         [
    //             'avrelia/backend'
    //         ],
    //         $this->librarian->resolve_all('avrelia/backend')
    //     );
    // }

    // public function test_resolve_all_no_match()
    // {
    //     $this->assertEquals(
    //         [],
    //         $this->librarian->resolve_all('*/non_existant')
    //     );
    // }

    public function test_factory()
    {
        $object = $this->librarian->factory('ns1/pac1');
        $this->assertInstanceOf('Ns1\\Pac1', $object);
    }

    public function test_dependencies_factory()
    {
        $dependencies = $this->librarian->dependencies_factory('ns2/paca');
        $this->assertInstanceOf('Ns1\\Pac2', $dependencies['pac2']);
    }

    public function test_construct_setup()
    {
        $setup = $this->librarian->construct_setup('ns1/pac1');
        $this->assertInstanceOf('Ns1\\Pac1\\Setup', $setup);
    }

    public function test_construct_setup_no_file()
    {
        $setup = $this->librarian->construct_setup('ns1/pac2');
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
        $this->assertTrue($this->librarian->load('ns1/pac1'));
    }

    public function test_load_non_existing()
    {
        $this->assertFalse($this->librarian->load('vendor/package'));
    }

    public function test_call()
    {
        $this->assertEquals(
            'hi',
            $this->librarian->call('ns1/pac1', 'say_hi')
        );
    }

    public function test_call_params()
    {
        $random_numer = rand(0, 100);
        $this->assertEquals(
            'The random number is: ' . $random_numer,
            $this->librarian->call('ns1/pac1', 'say_number', [$random_numer])
        );
    }

    public function test_is_enabled()
    {
        $this->assertTrue(
            $this->librarian->is_enabled('ns1/pac1')
        );
        $this->assertFalse(
            $this->librarian->is_enabled('ns2/pacb')
        );
        $this->assertFalse(
            // Non-existant
            $this->librarian->is_enabled('vendor/package')
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
            $this->librarian->get_dependencies('ns2/paca')
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
            $this->librarian->get_dependencies('ns2/pacb')
        );
    }

    // Deep scan only for disabled packages!
    // public function test_get_dependencies_deep()
    // {
    //     dump($this->librarian->get_dependencies('ns2/paca', true));
    //     $this->assertEquals(
    //         [
    //             'enabled' => [
    //                 'ns1/pac1' => '>= 1',
    //                 'ns1/pac2' => '>= 1'
    //             ],
    //             'disabled' => [],
    //             'missing'  => []
    //         ],
    //         $this->librarian->get_dependencies('ns2/paca', true)
    //     );
    // }

    public function test_get_dependees()
    {
        $this->assertEquals(
            ['ns1/pac2'],
            $this->librarian->get_dependees('ns1/pac1')
        );
    }

    // public function test_get_dependees_deep()
    // {
    //     $this->assertEquals(
    //         ['mysli/backend', 'mysli/session'],
    //         $this->librarian->get_dependees('mysli/users', true)
    //     );
    // }
}

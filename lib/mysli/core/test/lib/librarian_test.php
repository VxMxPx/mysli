<?php

namespace Mysli\Core\Lib;

include(__DIR__.'/../../core.php');
\Mysli\Core::init(
    __DIR__.'/public_dummy',
    __DIR__.'/libraries_dummy',
    __DIR__.'/data_dummy'
);

class LibrarianTest extends \PHPUnit_Framework_TestCase
{
    public function test_get_enabled()
    {
        $this->assertEquals(
            json_decode(file_get_contents(__DIR__.'/data_dummy/core/libraries.json'), true),
            Librarian::get_enabled()
        );
    }

    public function test_get_disabled_simple()
    {
        $disabled = Librarian::get_disabled();

        $this->assertTrue(is_array($disabled));
        $this->assertTrue(!empty($disabled));
        $this->assertTrue(isset($disabled['mysli/manage_settings']));
    }

    public function test_get_disabled_detailed()
    {
        $disabled = Librarian::get_disabled(true);

        $this->assertEquals(
            'mysli/manage_settings',
            $disabled['mysli/manage_settings']['library']
        );
    }

    public function test_get_details_enabled()
    {
        $this->assertEquals(
            'mysli/core',
            Librarian::get_details('mysli/core')['library']
        );
    }

    public function test_get_details_disabled()
    {
        $this->assertEquals(
            'mysli/manage_settings',
            Librarian::get_details('mysli/manage_settings')['library']
        );
    }

    public function test_get_details_nonexisting()
    {
        $this->assertEquals(
            [],
            Librarian::get_details('vendor/library')
        );
    }

    public function test_get_satisfied_normal()
    {
        $this->assertEquals(
            'mysli/core',
            Librarian::get_satisfied('mysli/core', '>= 0.1')
        );
    }

    public function test_get_satisfied_regex()
    {
        $this->assertEquals(
            'mysli/core',
            Librarian::get_satisfied('*/core', '= 0.1')
        );
    }

    public function test_get_satisfied_version_failed()
    {
        $this->assertFalse(Librarian::get_satisfied('mysli/core', '> 0.1'));
        $this->assertFalse(Librarian::get_satisfied('mysli/core', '< 0.1'));
        $this->assertFalse(Librarian::get_satisfied('mysli/core', '= 0.2'));
        $this->assertFalse(Librarian::get_satisfied('mysli/core', '>= 0.2'));
        $this->assertFalse(Librarian::get_satisfied('mysli/core', '<= 0.05'));
    }

    public function test_get_satisfied_name_failed()
    {
        $this->assertFalse(
            Librarian::get_satisfied('vendor/library', '> 0.1')
        );
    }

    public function test_dependencies_factory()
    {
        $dependencies_list = Librarian::dependencies_factory([
            '*/core'    => '>= 0.1',
            'mysli/mjs' => '>= 0.1',
            '*/session' => '>= 0.1'
        ]);

        $this->assertFalse($dependencies_list['core']);
        $this->assertTrue(
            is_a($dependencies_list['mjs'], 'Mysli\\Mjs')
        );
        $this->assertTrue(
            is_a($dependencies_list['session'], 'Mysli\\Session')
        );
    }

    public function test_need_enabled()
    {
        $this->assertEquals(
            [],
            Librarian::need_enabled('mysli/frontend')
        );
        $this->assertEquals(
            [
                '*/mailer'    => '>= 0.0',
                'avrelia/sql' => '>= 1.0'
            ],
            Librarian::need_enabled('avrelia/dummy')
        );
    }

    public function test_need_disabled()
    {
        $this->assertEquals(
            [],
            Librarian::need_disabled('mysli/dot')
        );
        $this->assertEquals(
            [
                'mysli/backend'
            ],
            Librarian::need_disabled('mysli/session')
        );
    }

    public function test_construct_setup()
    {
        $setup = Librarian::construct_setup('mysli/mjs');
        $this->assertTrue(is_a($setup, 'Mysli\\Mjs\\Setup'));
    }

}
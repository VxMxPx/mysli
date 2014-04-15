<?php

namespace Mysli\Pkgm;

include(__DIR__.'/_common.php');

Generator::drop_packages();
Generator::generate_packages();

class RegistryTest extends \PHPUnit_Framework_TestCase
{
    private $registry;

    public function __construct()
    {
        file_put_contents(datpath('registry.json'), json_encode(['enabled' => [], 'roles' => []]));
        $this->registry = new Registry(datpath('registry.json'));
    }

    private function get_control($package)
    {
        return new Control($package, $this->registry);
    }

    // is_enabled --------------------------------------------------------------

    public function test_is_enabled_not()
    {
        $this->assertFalse($this->registry->is_enabled('mysliio/core'));
    }

    public function test_is_enabled_yes()
    {
        $this->get_control('mysliio/core')->enable();
        $this->assertTrue($this->registry->is_enabled('mysliio/core'));
    }

    public function test_is_enabled_role()
    {
        $this->get_control('mysliio/core')->enable();
        $this->assertTrue($this->registry->is_enabled('@core'));
    }

    // exists ------------------------------------------------------------------

    public function test_exists_not()
    {
        $this->assertFalse($this->registry->exists('mysliio/null'));
    }

    public function test_exists_dir_not()
    {
        $this->assertFalse($this->registry->exists('mysliio'));
    }

    public function test_exists_yes()
    {
        $this->assertTrue($this->registry->exists('mysliio/core'));
    }

    public function test_exists_role()
    {
        $this->assertTrue($this->registry->exists('@core'));
    }

    // list_enabled ------------------------------------------------------------

    public function test_list_enabled()
    {
        $this->assertEquals(
            [],
            $this->registry->list_enabled()
        );

        $this->assertEquals(
            [],
            $this->registry->list_enabled(true)
        );
    }

    public function test_list_enabled_entries()
    {
        $this->get_control('mysliio/core')->enable();
        $this->get_control('mysliio/pkgm')->enable();

        $this->assertEquals(
            ['mysliio/core', 'mysliio/pkgm'],
            $this->registry->list_enabled()
        );

        $this->assertEquals(
            'mysliio/core',
            $this->registry->list_enabled(true)['mysliio/core']['package']
        );
    }

    // list_disabled -----------------------------------------------------------

    public function test_list_disabled()
    {
        $this->assertCount(
            9,
            $this->registry->list_disabled()
        );

        $this->assertEquals(
            'mysliio/core',
            $this->registry->list_disabled(true)['mysliio/core']['package']
        );
    }

    // get_details -------------------------------------------------------------

    public function test_get_details_enabled()
    {
        $this->get_control('mysliio/core')->enable();
        $this->assertEquals(
            'mysliio/core',
            $this->registry->get_details('mysliio/core')['package']
        );
    }

    public function test_get_details_disabled()
    {
        $this->assertEquals(
            'mysliio/core',
            $this->registry->get_details('mysliio/core')['package']
        );
    }

    /**
     * @expectedException \Core\NotFoundException
     */
    public function test_non_existant()
    {
        $this->registry->get_details('mysliio/null');
    }

    // list_dependencies -------------------------------------------------------

    /**
     * @expectedException \Mysli\Pkgm\DependencyException
     */
    public function test_list_dependencies_corss_dependencies()
    {
        // Create cross dependencies
        $json = json_decode( file_get_contents(pkgpath('mysliio/core/meta.json')), true );
        $json = array_merge_recursive($json, [
            'depends_on' => ['@pkgm' => '>= 1']
        ]);
        file_put_contents( pkgpath('mysliio/core/meta.json') , json_encode($json) );

        try {
            $this->registry->list_dependencies('avrelia/dash', true);
        } catch (\Exception $e) {
            Generator::drop_packages();
            Generator::generate_packages();
            throw $e;
        }
    }

    public function test_list_dependencies()
    {
        $this->assertEquals(
            [
                'enabled'  => [],
                'disabled' => [
                    '@core'           => '>= 1',
                    'mysliio/config'  => '>= 1',
                    'avrelia/users'   => '>= 1',
                    '@event'          => '>= 1',
                    'avrelia/web'     => '>= 1',
                    'avrelia/session' => '>= 1',
                ],
                'missing'  => []
            ],
            $this->registry->list_dependencies('avrelia/dash', true)
        );
    }

    public function test_list_dependencies_missing()
    {
        $this->assertEquals(
            [
                'enabled'  => [],
                'disabled' => [
                    '@core' => '>= 1'
                ],
                'missing'  => [
                    'avrelia/non_existant' => '>= 1'
                ]
            ],
            $this->registry->list_dependencies('avrelia/bad', true)
        );
    }

    public function test_list_dependencies_enabled()
    {
        $this->get_control('mysliio/core')->enable();

        $this->assertEquals(
            [
                'enabled'  => [
                    '@core'           => '>= 1',
                ],
                'disabled' => [
                    'mysliio/config'  => '>= 1',
                    'avrelia/users'   => '>= 1',
                    '@event'          => '>= 1',
                    'avrelia/web'     => '>= 1',
                    'avrelia/session' => '>= 1',
                ],
                'missing'  => []
            ],
            $this->registry->list_dependencies('avrelia/dash', true)
        );
    }

    // list_dependees -------------------------------------------------------

    public function test_list_dependees()
    {
        $dependencies = $this->registry->list_dependencies('avrelia/dash', true)['disabled'];

        foreach ($dependencies as $dependency => $version)
            $this->get_control($dependency)->enable();

        $this->get_control('avrelia/dash')->enable();

        $this->assertEquals(
            [
                'avrelia/dash',
                'avrelia/session',
                'avrelia/web',
                'mysliio/config',
                'mysliio/event',
            ],
            $this->registry->list_dependees('mysliio/core', true)
        );
    }

    // list_obsolete -----------------------------------------------------------

    public function test_list_obsolete()
    {
        // Enable Dash dep.
        $dependencies = $this->registry->list_dependencies('avrelia/dash', true)['disabled'];
        foreach ($dependencies as $dependency => $version)
            $this->get_control($dependency)->enable('avrelia/dash');

        $this->assertEquals(
            [
                'avrelia/web',
                'avrelia/session',
                'avrelia/users',
                'mysliio/event',
                'mysliio/config',
                'mysliio/core'
            ],
            $this->registry->list_obsolete()
        );
    }

    public function test_list_obsolete_some()
    {
        // Enable session dep. + session
        $dependencies = $this->registry->list_dependencies('avrelia/session', true)['disabled'];
        foreach ($dependencies as $dependency => $version)
            $this->get_control($dependency)->enable('avrelia/session');
        $this->get_control('avrelia/session')->enable();

        // Enable Dash dep.
        $dependencies = $this->registry->list_dependencies('avrelia/dash', true)['disabled'];
        foreach ($dependencies as $dependency => $version)
            $this->get_control($dependency)->enable('avrelia/dash');

        $this->assertEquals(
            [
                'avrelia/web'
            ],
            $this->registry->list_obsolete()
        );
    }

    // remove_package ----------------------------------------------------------

    public function test_remove_package()
    {
        $this->get_control('mysliio/core')->enable();
        $this->assertTrue($this->registry->is_enabled('mysliio/core'));
        $this->registry->remove_package('mysliio/core');
        $this->assertFalse($this->registry->is_enabled('mysliio/core'));
    }

    // add_package -------------------------------------------------------------

    public function test_add_package()
    {
        $this->registry->add_package('mysliio/test', ['testme!']);

        $this->assertEquals(
            ['testme!'],
            $this->registry->get_details('mysliio/test')
        );
    }

    // add_dependee ------------------------------------------------------------

    public function test_add_dependee()
    {
        $this->registry->add_package('mysliio/test', [
            'required_by' => []
        ]);

        $this->registry->add_dependee('mysliio/test', 'mysliio/test_dependee');

        $this->assertEquals(
            [
                'required_by' => [
                    'mysliio/test_dependee'
                ]
            ],
            $this->registry->get_details('mysliio/test')
        );
    }

    // remove_dependee ---------------------------------------------------------

    public function test_remove_dependee()
    {
        $this->registry->add_package('mysliio/test', [
            'required_by' => []
        ]);

        $this->registry->add_dependee('mysliio/test', 'mysliio/test_dependee');

        $this->assertEquals(
            [
                'required_by' => [
                    'mysliio/test_dependee'
                ]
            ],
            $this->registry->get_details('mysliio/test')
        );

        $this->registry->remove_dependee('mysliio/test', 'mysliio/test_dependee');

        $this->assertEquals(
            [
                'required_by' => []
            ],
            $this->registry->get_details('mysliio/test')
        );
    }

    // set_role + get_role -----------------------------------------------------

    public function test_set_role()
    {
        $this->registry->set_role('@test', 'mysliio/test');
        $this->assertEquals(
            'mysliio/test',
            $this->registry->get_role('@test')
        );
    }

    // unset role --------------------------------------------------------------

    public function test_unset_role()
    {
        $this->registry->set_role('@test', 'mysliio/test');
        $this->assertEquals(
            'mysliio/test',
            $this->registry->get_role('@test')
        );
        $this->registry->unset_role('@test');
        $this->assertNull($this->registry->get_role('@test'));
    }

}

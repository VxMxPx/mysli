<?php

namespace Mysli\Pkgm;

include(__DIR__.'/../util.php');         // Include self
include(__DIR__.'/../../core/core.php'); // CORE is needed!
new \Mysli\Core\Core(
    realpath(__DIR__.'/dummy/private'),
    realpath(__DIR__.'/dummy/packages')
);

class UtilTest extends \PHPUnit_Framework_TestCase
{
    // to_path -----------------------------------------------------------------
    public function test_to_path()
    {
        $this->assertEquals(
            'vendor/package/package.php',
            Util::to_path('vendor/package')
        );
        $this->assertEquals(
            'vendor/package/package.php',
            Util::to_path('vendor/package')
        );
        $this->assertEquals(
            'vendor/package/package.php',
            Util::to_path('\\Vendor\\Package\\Package')
        );
        $this->assertEquals(
            'vendor/package/sub_class/sub_path.php',
            Util::to_path('\\Vendor\\Package\\SubClass\\SubPath')
        );
    }

    // to_class ----------------------------------------------------------------
    public function test_to_class()
    {
        $package = 'vendor/package';

        $this->assertEquals(
            '\\Vendor\\Package\\Package',
            Util::to_class($package, Util::FULL)
        );
        $this->assertEquals(
            '\\Vendor\\Package',
            Util::to_class($package, Util::BASE)
        );
        $this->assertEquals(
            'Package',
            Util::to_class($package, Util::FILE)
        );
    }

    public function test_to_class_complex()
    {
        $package = 'vendor/package/sub_dir/sub_class';

        $this->assertEquals(
            '\\Vendor\\Package\\SubDir\\SubClass',
            Util::to_class($package, Util::FULL)
        );
        $this->assertEquals(
            '\\Vendor\\Package',
            Util::to_class($package, Util::BASE)
        );
        $this->assertEquals(
            'SubDir\\SubClass',
            Util::to_class($package, Util::FILE)
        );
    }

    public function test_to_class_from_class()
    {
        $package = '\\Vendor\\Package\\SubDir\\SubClass';

        $this->assertEquals(
            '\\Vendor\\Package\\SubDir\\SubClass',
            Util::to_class($package, Util::FULL)
        );
        $this->assertEquals(
            '\\Vendor\\Package',
            Util::to_class($package, Util::BASE)
        );
        $this->assertEquals(
            'SubDir\\SubClass',
            Util::to_class($package, Util::FILE)
        );
    }

    // to_pkg ------------------------------------------------------------------
    public function test_to_pkg()
    {
        $class = '\\Vendor\\Package\\Package';

        $this->assertEquals(
            'vendor/package/package',
            Util::to_pkg($class, Util::FULL)
        );
        $this->assertEquals(
            'vendor/package',
            Util::to_pkg($class, Util::BASE)
        );
        $this->assertEquals(
            'package',
            Util::to_pkg($class, Util::FILE)
        );
    }

    public function test_to_pkg_complex()
    {
        $class = '\\Vendor\\Package\\SubDir\\SubClass';

        $this->assertEquals(
            'vendor/package/sub_dir/sub_class',
            Util::to_pkg($class, Util::FULL)
        );
        $this->assertEquals(
            'vendor/package',
            Util::to_pkg($class, Util::BASE)
        );
        $this->assertEquals(
            'sub_dir/sub_class',
            Util::to_pkg($class, Util::FILE)
        );
    }

    public function test_to_pkg_from_pkg()
    {
        $class = 'vendor/package/sub_dir/sub_class';

        $this->assertEquals(
            'vendor/package/sub_dir/sub_class',
            Util::to_pkg($class, Util::FULL)
        );
        $this->assertEquals(
            'vendor/package',
            Util::to_pkg($class, Util::BASE)
        );
        $this->assertEquals(
            'sub_dir/sub_class',
            Util::to_pkg($class, Util::FILE)
        );
    }
}

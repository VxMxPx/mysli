<?php

namespace Mysli\Core\Lib;

include(__DIR__.'/../../core.php');
\Mysli\Core::init(
    __DIR__.'/public_dummy',
    __DIR__.'/libraries_dummy',
    __DIR__.'/data_dummy'
);

class IntTest extends \PHPUnit_Framework_TestCase
{
    public function test_compare_versions()
    {
        $this->assertTrue(Int::compare_versions(1.0, '>= 1.0'));
        $this->assertFalse(Int::compare_versions(1.0, '> 1.0'));
        $this->assertFalse(Int::compare_versions(1.0, '< 1.0'));
        $this->assertTrue(Int::compare_versions(1.0, '<= 1.0'));
        $this->assertTrue(Int::compare_versions(1.0, '= 1.0'));

        $this->assertFalse(Int::compare_versions(1.0, '>= 2.0'));
        $this->assertFalse(Int::compare_versions(1.0, '> 2.0'));
        $this->assertTrue(Int::compare_versions(1.0, '< 2.0'));
        $this->assertTrue(Int::compare_versions(1.0, '<= 2.0'));
        $this->assertFalse(Int::compare_versions(1.0, '= 2.0'));
    }
}

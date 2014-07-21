<?php

namespace Mysli\Core\Util;

// Exceptions, etc..
include(__DIR__.'/../core.php');
$core = new \Mysli\Core\Core(__DIR__.'/dummy', __DIR__.'/dummy');

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

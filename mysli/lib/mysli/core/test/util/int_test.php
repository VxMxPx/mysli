<?php

namespace Mysli\Core\Util;

include(__DIR__.'/../../core.php');
new \Mysli\Core(
    __DIR__.'/../dummy/public',
    __DIR__.'/../dummy/libraries',
    __DIR__.'/../dummy/data'
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

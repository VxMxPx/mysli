<?php

namespace mysli\core\test;

include(__DIR__ . '/../src/core.php');

use mysli\core\core as core;
core::init(realpath(__DIR__), realpath(__DIR__.'/../../../'));

class core_test extends \PHPUnit_Framework_TestCase
{
    function test_init() {
        $this->assertEquals(
            realpath(__DIR__),
            MYSLI_DATPATH);
        $this->assertEquals(
            realpath(__DIR__.'/../../../'),
            MYSLI_PKGPATH);
        $this->assertEquals(
            ['mysli\\core\\core', 'autoload'],
            spl_autoload_functions()[1]);
    }
    /**
     * @expectedException   \mysli\core\exception\base
     * @expectedExceptionCode 2
     */
    function test_autoload() {
        throw new \mysli\core\exception\base(null, 2);
    }
}

<?php

namespace mysli\cli\test;

include(__DIR__.'/../../core/src/core.php');
include(__DIR__.'/../../type/src/str.php');
include(__DIR__.'/../../type/src/arr.php');
include(__DIR__.'/../src/param.php');

class param_test extends \PHPUnit_Framework_TestCase
{
	function test_instance() {
		$param = new \mysli\cli\param('Testing');
		$this->assertInstanceOf('\\mysli\\cli\\param', $param);
	}
}
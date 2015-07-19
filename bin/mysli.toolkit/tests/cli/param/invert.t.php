<?php

#: Before
use mysli\toolkit\cli\param;

#: Test Inverted Boolean, True
#: Expect False
$param = new param(null);
$param->type('boolean');
$param->invert(true);
$param->set_value(true);
return $param->get_value();

#: Test Inverted String
#: Expect String "!olleH"
$param = new param(null);
$param->invert(true);
$param->set_value("Hello!");
return $param->get_value();

#: Test Inverted Float
#: Expect Float -12.3
$param = new param(null);
$param->type('float');
$param->invert(true);
$param->set_value(12.3);
return $param->get_value();

#: Test Inverted Integer
#: Expect Integer -12
$param = new param(null);
$param->type('integer');
$param->invert(true);
$param->set_value(12);
return $param->get_value();

#: Test Inverted Array
$param = new param(null);
$param->type('array');
$param->invert(true);
$param->set_value('foo,bar,baz');
return assert::equals($param->get_value(), ['baz', 'bar', 'foo']);

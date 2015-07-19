<?php

#: Before
use mysli\toolkit\cli\param;

#: Test Boolean, True
#: Expect True
$param = new param(null);
$param->type('boolean');
$param->set_value(true);
return $param->get_value();

#: Test Float
#: Expect Float 12.3
$param = new param(null);
$param->type('float');
$param->set_value(12.3);
return $param->get_value();

#: Test Integer
#: Expect Integer 12
$param = new param(null);
$param->type('integer');
$param->set_value(12);
return $param->get_value();

#: Test Array
$param = new param(null);
$param->type('array');
$param->set_value('foo,bar,baz');
return assert::equals($param->get_value(), ['foo', 'bar', 'baz']);

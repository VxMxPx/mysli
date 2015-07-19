<?php

#: Before
use mysli\toolkit\cli\param;

#: Test Constructor Long
#: Expect String "name"
$param = new param('--name/-s');
return $param->option('long');


#: Test Constructor Short
#: Expect String "s"
$param = new param('--name/-s');
return $param->option('short');


#: Test Constructor ID
#: Expect String "--name/-s"
$param = new param('--name/-s');
return $param->option('id');


#: Test Constructor Name
#: Expect String "name"
$param = new param('--name/-s');
return $param->option('name');


#: Test Constructor Name When Only Short
#: Expect String "s"
$param = new param('-s');
return $param->option('name');


#: Test Constructor Name When Positonal
#: Expect String "positional"
$param = new param('POSITIONAL');
return $param->option('name');


#: Test Constructor Long When No Long
#: Expect Null
$param = new param('-s');
return $param->option('long');


#: Test Constructor Short When No Short
#: Expect Null
$param = new param('--long');
return $param->option('short');

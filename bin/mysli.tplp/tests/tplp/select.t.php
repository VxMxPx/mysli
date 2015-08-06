<?php

#: Before
use mysli\tplp\tplp;
use mysli\toolkit\fs;

#: Test Instance
#: Expect Instance mysli\tplp\template
return tplp::select(fs::tmppath('dev.test'));

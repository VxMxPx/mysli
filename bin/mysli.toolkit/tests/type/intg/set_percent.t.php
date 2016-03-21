<?php

#: Before
use mysli\toolkit\type\intg;

#: Test Basic
#: Expect Integer 25
return intg::set_percent(25, 100);

#: Test Float
#: Expect Float 16.12
return intg::set_percent(13, 124);

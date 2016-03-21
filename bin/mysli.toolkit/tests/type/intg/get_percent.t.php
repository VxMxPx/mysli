<?php

#: Before
use mysli\toolkit\type\intg;

#: Test Basic
#: Expect Float 25
return intg::get_percent(25, 100);

#: Test Float
#: Expect Float 2.6047565118912797
return intg::get_percent(345, 13245);

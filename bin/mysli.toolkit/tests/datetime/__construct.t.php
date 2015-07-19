<?php

#: Before
use mysli\toolkit\datetime;

#: Test Instance
#: Expect Instance mysli\toolkit\datetime
return new datetime();

#: Test Exception
#: Expect Exception \Exception 0
new datetime(['Error']);

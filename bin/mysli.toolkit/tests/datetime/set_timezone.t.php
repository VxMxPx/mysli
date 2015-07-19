<?php

#: Before
use mysli\toolkit\datetime;

#: Test Change Timezone
#: Use Instance
#: Expect String "2014-08-10 14:00:10"
$datetime = new datetime('2014-08-10 12:00:10', 'UTC');
$datetime->set_timezone('Europe/Ljubljana');
return $datetime->format('Y-m-d H:i:s');

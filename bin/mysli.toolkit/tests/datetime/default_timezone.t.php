<?php

#: Before
use mysli\toolkit\datetime;

#: Test Get Default Timezone
#: Expect String "UTC"
date_default_timezone_set('UTC');
return datetime::get_default_timezone();

#: Test Get/Set Default Timezone
#: Expect String "Europe/Ljubljana"
datetime::set_default_timezone('Europe/Ljubljana');
return datetime::get_default_timezone();

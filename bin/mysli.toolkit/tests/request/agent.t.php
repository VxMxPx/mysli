<?php

#: Before
use mysli\toolkit\request;
__test_set_server();

#: Test Agent
#: Expect String Mozilla/5.0 (X11; Linux x86_64)
return request::agent();

<?php

#: Before
use mysli\toolkit\request;
__test_set_server();

#: Test Host
#: Expect String domain.tld
return request::host();

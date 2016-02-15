<?php

#: Before
use mysli\toolkit\request;
__test_set_server();

#: Test Segment, 1
#: Expect String seg1
return request::segment(0);

#: Test Segment, 2
#: Expect String seg2
return request::segment(1);

#: Test Segment, All
return assert::equals(
    request::segment(),
    [ 'seg1', 'seg2' ]);

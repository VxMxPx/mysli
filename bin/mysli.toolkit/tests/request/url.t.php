<?php

#: Before
use mysli\toolkit\request;
__test_set_server();

#: Test Url, Default
#: Expect String http://domain.tld
return request::url();

#: Test Url, With Query
#: Expect String http://domain.tld/index.php/seg1/seg2?get1=val1&get2=val2
return request::url(true);

#: Test Url, With NS Port
#: Expect String http://domain.tld:8000
$_SERVER['SERVER_PORT'] = '8000';
return request::url(false, true);

#: Test Url, With Query and NS Port
#: Expect String http://domain.tld:8000/index.php/seg1/seg2?get1=val1&get2=val2
$_SERVER['SERVER_PORT'] = '8000';
return request::url(true, true);

#: Test Url, Sub Domain
#: Expect String http://si.domain.tld
return request::url(false, false, 'si');

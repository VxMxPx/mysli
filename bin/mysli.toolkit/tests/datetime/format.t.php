<?php

#: Before
use mysli\toolkit\datetime;

#: Define Instance
$datetime = new datetime("2003-12-24 22:00:10", "UTC");

#: Test Timestamp
#: Use Instance
#: Expect String "1072303210"
return $datetime->format(datetime::timestamp);

#: Test Time
#: Use Instance
#: Expect String "22:00:10"
return $datetime->format(datetime::time);

#: Test Day
#: Use Instance
#: Expect String "24"
return $datetime->format(datetime::day);

#: Test Month
#: Use Instance
#: Expect String "12"
return $datetime->format(datetime::month);

#: Test Year
#: Use Instance
#: Expect String "2003"
return $datetime->format(datetime::year);

#: Test Sort
#: Use Instance
#: Expect String "20031224220010"
return $datetime->format(datetime::sort);

#: Test Atom
#: Use Instance
#: Expect String "2003-12-24T22:00:10+00:00"
return $datetime->format(datetime::atom);

#: Test Cookie
#: Use Instance
#: Expect String "Wednesday, 24-Dec-2003 22:00:10 UTC"
return $datetime->format(datetime::cookie);

#: Test iso8601
#: Use Instance
#: Expect String "2003-12-24T22:00:10+0000"
return $datetime->format(datetime::iso8601);

#: Test rfc822
#: Use Instance
#: Expect String "Wed, 24 Dec 03 22:00:10 +0000"
return $datetime->format(datetime::rfc822);

#: Test rfc850
#: Use Instance
#: Expect String "Wednesday, 24-Dec-03 22:00:10 UTC"
return $datetime->format(datetime::rfc850);

#: Test rfc1036
#: Use Instance
#: Expect String "Wed, 24 Dec 03 22:00:10 +0000"
return $datetime->format(datetime::rfc1036);

#: Test rfc1123
#: Use Instance
#: Expect String "Wed, 24 Dec 2003 22:00:10 +0000"
return $datetime->format(datetime::rfc1123);

#: Test rfc2822
#: Use Instance
#: Expect String "Wed, 24 Dec 2003 22:00:10 +0000"
return $datetime->format(datetime::rfc2822);

#: Test rfc3339
#: Use Instance
#: Expect String "2003-12-24T22:00:10+00:00"
return $datetime->format(datetime::rfc3339);

#: Test rss
#: Use Instance
#: Expect String "Wed, 24 Dec 2003 22:00:10 +0000"
return $datetime->format(datetime::rss);

#: Test w3c
#: Use Instance
#: Expect String "2003-12-24T22:00:10+00:00"
return $datetime->format(datetime::w3c);

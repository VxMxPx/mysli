<?php

#: Before
use mysli\toolkit\type\str;

#: Test Cut Basic
#: Expect String "Lorem i..."
return str::cut_pad('Lorem ipsum dolor', 10);

#: Test Pad Basic
#: Expect String "Lorem ipsum dolor   "
return str::cut_pad('Lorem ipsum dolor', 20);

#: Test Cut, No Add
#: Expect String "Lorem ipsu"
return str::cut_pad('Lorem ipsum dolor', 10, null, null);

#: Test Pad, Costume Add
#: Expect String "Lorem ipsum dolor:::"
return str::cut_pad('Lorem ipsum dolor', 20, null, '::');

#: Test Cut, Costume Add
#: Expect String "Lorem ip--"
return str::cut_pad('Lorem ipsum dolor', 10, '--');

#: Test Exact Length
#: Expect String "Lorem ipsum dolor"
return str::cut_pad('Lorem ipsum dolor', 17);

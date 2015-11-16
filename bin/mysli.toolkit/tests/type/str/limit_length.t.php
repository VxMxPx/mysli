<?php

#: Before
use mysli\toolkit\type\str;

#: Test Basic
#: Expect String "Lorem ipsum dol"
return str::limit_length('Lorem ipsum dolor sit amet consectetur.', 15);

#: Test Ellipsis
#: Expect String "Lorem ipsum ..."
return str::limit_length('Lorem ipsum dolor sit amet consectetur.', 15, '...');

#: Test Too Short
#: Expect String "Lorem ipsum dolor."
return str::limit_length('Lorem ipsum dolor.', 30, '...');

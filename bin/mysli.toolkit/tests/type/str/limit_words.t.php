<?php

#: Before
use mysli\toolkit\type\str;

#: Test Basic
#: Expect String "Lorem ipsum dolor"
return str::limit_words('Lorem ipsum dolor sit amet consectetur.', 3);

#: Test Ellipsis
#: Expect String "Lorem ipsum dolor..."
return str::limit_words('Lorem ipsum dolor sit amet consectetur.', 3, '...');

#: Test Too Short
#: Expect String "Lorem ipsum dolor."
return str::limit_words('Lorem ipsum dolor.', 3, '...');

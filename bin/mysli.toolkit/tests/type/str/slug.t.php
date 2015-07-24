<?php

#: Before
use mysli\toolkit\type\str;

#: Test Basic
#: Expect String "hello-world-12"
return str::slug('Hello World (12)!!');

#: Test Multiple Repeat
#: Expect String "hello-world"
return str::slug('hello---world');

#: Test Special Glue
#: Expect String "hello_world_12"
return str::slug('Hello World (12)!!', '_');

#: Test Special Glue, Double
#: Expect String "hello__world__12"
return str::slug('Hello World (12)!!', '__');

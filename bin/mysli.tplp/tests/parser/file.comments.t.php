<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;
use mysli\toolkit\fs\fs;

#: Define File
$file = <<<'FILE'
<html>
<body>
    a{* Comment *} b{* Comment *}
    c{* Multi
    Line
    Comment *} d{* Comment *}

    {{{
    // Protected region
    <script>
        if (true) { return false; } else { return true; }
        /*
        ::if something == true
        ::/if
        */
    </script>
    }}}
</body>
</html>
FILE;

#: Test Comments
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#: Use File
$processed = parser::file(
    '~test.tpl.html', fs::tmppath('dev.test'), [ '~test' => $file ]
);
return assert::equals(
    $processed,
    <<<'EXPECT'
<?php
namespace tplp\template\test;
?><html>
<body>
    a b
    c
 d

    // Protected region
    <script>
        if (true) { return false; } else { return true; }
        /*
        ::if something == true
        ::/if
        */
    </script>
</body>
</html>
EXPECT
);

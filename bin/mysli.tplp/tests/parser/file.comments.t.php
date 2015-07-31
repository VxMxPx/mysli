<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;
use mysli\toolkit\fs\fs;
use mysli\toolkit\fs\file;

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
file::write(fs::tmppath('dev.test/~test.tpl.html'), $file);


#: After
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
file::remove(fs::tmppath('dev.test/~test.tpl.html'));


#: Test Comments
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$processed = parser::file('~test.tpl.html', fs::tmppath('dev.test'));
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

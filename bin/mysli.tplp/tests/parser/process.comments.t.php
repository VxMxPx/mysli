<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\tplp\parser;

#: Test Comments
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$template = <<<'TEMPLATE'
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
TEMPLATE;
$parser = new parser();
return assert::equals(
    $parser->process($template),
    <<<'EXPECT'
<html>
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

--TEST--
Comments
--FILE--
<?php
use mysli\util\tplp\parser;

$input = <<<INPUT
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
INPUT;

print_r(parser::process($input));
?>
--EXPECT--
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

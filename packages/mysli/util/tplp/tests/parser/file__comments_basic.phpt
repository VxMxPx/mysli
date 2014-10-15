--TEST--
--VIRTUAL (test.tplp)--
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
--FILE--
<?php
use mysli\util\tplp\parser;
print_r(parser::file('test.tplp', __DIR__));
?>
--EXPECT--
<?php
namespace tplp\generic\test;
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

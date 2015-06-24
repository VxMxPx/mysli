--TEST--
--VIRTUAL (test.tplp)--
<!DOCTYPE html>
<html>
<head>
    <title>{title}</title>
</head>
<body>
    ::import sidebar from modules do
        ::set before
            <p>Before...</p>
        ::/set
        ::set after
            <p>After...</p>
        ::/set
    ::/import
</body>
</html>
--VIRTUAL (modules.tplp)--
::module sidebar
<div class="sidebar">
    ::print before
    <p>Hello world!</p>
    ::print after
</div>
::/module
--FILE--
<?php
use mysli\util\tplp\parser;
print_r(parser::file('test.tplp', __DIR__));
?>
--EXPECT--
<?php
namespace tplp\generic\test;
?><!DOCTYPE html>
<html>
<head>
    <title><?php echo $title; ?></title>
</head>
<body>
<div class="sidebar">
            <p>Before...</p>
    <p>Hello world!</p>
            <p>After...</p>
</div>
</body>
</html>

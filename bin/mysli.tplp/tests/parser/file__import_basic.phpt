--TEST--
--VIRTUAL (test.tplp)--
<!DOCTYPE html>
<html>
<head>
    <title>{title}</title>
</head>
<body>
    ::import sidebar
</body>
</html>
--VIRTUAL (sidebar.tplp)--
<div class="sidebar">
    <p>Hello world!</p>
</div>
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
    <p>Hello world!</p>
</div>
</body>
</html>

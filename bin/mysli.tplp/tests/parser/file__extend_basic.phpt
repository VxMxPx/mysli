--TEST--
--VIRTUAL (test.tplp)--
::extend ./layout set content
<div>
    Some content here...
</div>
--VIRTUAL (layout.tplp)--
<!DOCTYPE html>
<html>
<head>
    <title>{title}</title>
</head>
<body>
    ::print content
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
?><!DOCTYPE html>
<html>
<head>
    <title><?php echo $title; ?></title>
</head>
<body>
<div>
    Some content here...
</div>
</body>
</html>

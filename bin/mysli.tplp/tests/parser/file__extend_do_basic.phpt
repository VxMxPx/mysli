--TEST--
--VIRTUAL (test.tplp)--
::extend ./layout set content do
    ::set styles
    <link rel="stylesheet" type="text/css" href="main.css">
    <link rel="stylesheet" type="text/css" href="mobile.css">
    ::/set
::/extend
<div>
    Some content here...
</div>
--VIRTUAL (layout.tplp)--
<!DOCTYPE html>
<html>
<head>
    <title>{title}</title>
    ::print styles
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
    <link rel="stylesheet" type="text/css" href="main.css">
    <link rel="stylesheet" type="text/css" href="mobile.css">
</head>
<body>
<div>
    Some content here...
</div>
</body>
</html>

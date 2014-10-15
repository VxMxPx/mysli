--TEST--
--VIRTUAL (test.tplp)--
<!DOCTYPE html>
<html>
<head>
    <title>{title}</title>
</head>
<body>
    ::import non_file
</body>
</html>
--VIRTUAL (modules.tplp)--
::module sidebar
<div class="sidebar">
    <p>Hello world!</p>
</div>
::/module
--FILE--
<?php
use mysli\util\tplp\parser;
print_r(parser::file('test.tplp', __DIR__));
?>
--EXPECTF--
Fatal error: Uncaught exception 'mysli\util\tplp\exception\parser' with message 'File not found: ``
  4.     <title>{title}</title>
  5. </head>
  6. <body>
>>7.     ::import non_file
  8. </body>
  9. </html>
File: `emp/phpt/test.tplp`
' in %sparser.php:%d
Stack trace:
%a

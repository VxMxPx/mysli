--TEST--
Various types.
--FILE--
<?php
use \mysli\framework\ym\ym;

var_dump(ym::decode(<<<EOT
s1: I'm string!
s2: "I'm also a string"
s3: "113700"
i1: 42
f1: 11.23
b1: Yes
b2: No
b3: True
b4: False
a1:
    - An
    - array!
a2:
    associative : array
    key: value
a3:
    mixed: value
    - value
    - value2
EOT
));

?>
--EXPECT--
array(12) {
  ["s1"]=>
  string(11) "I'm string!"
  ["s2"]=>
  string(17) "I'm also a string"
  ["s3"]=>
  string(6) "113700"
  ["i1"]=>
  int(42)
  ["f1"]=>
  float(11.23)
  ["b1"]=>
  bool(true)
  ["b2"]=>
  bool(false)
  ["b3"]=>
  bool(true)
  ["b4"]=>
  bool(false)
  ["a1"]=>
  array(2) {
    [0]=>
    string(2) "An"
    [1]=>
    string(6) "array!"
  }
  ["a2"]=>
  array(2) {
    ["associative"]=>
    string(5) "array"
    ["key"]=>
    string(5) "value"
  }
  ["a3"]=>
  array(3) {
    ["mixed"]=>
    string(5) "value"
    [0]=>
    string(5) "value"
    [1]=>
    string(6) "value2"
  }
}

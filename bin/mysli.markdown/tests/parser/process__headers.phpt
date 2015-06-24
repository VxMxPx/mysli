--TEST--
--DESCRIPTION--
--FILE--
<?php
use mysli\util\markdown;

echo markdown::process(<<<EOF
# Header 1

## Header 2

### Header 3

#### Header 4

##### Header 5

###### Header 6

# Header 1 #

## Header 2 ##

### Header 3 ###

#### Header 4 ####

##### Header 5 #####

###### Header 6 ######

# Header 1 ######

## Header 2 ######

### Header 3 ######

#### Header 4 ######

##### Header 5 ######

###### Header 6 ######

EOF
);
?>
--EXPECT--
<h1>Header 1</h1>
<h2>Header 2</h2>
<h3>Header 3</h3>
<h4>Header 4</h4>
<h5>Header 5</h5>
<h6>Header 6</h6>
<h1>Header 1</h1>
<h2>Header 2</h2>
<h3>Header 3</h3>
<h4>Header 4</h4>
<h5>Header 5</h5>
<h6>Header 6</h6>
<h1>Header 1</h1>
<h2>Header 2</h2>
<h3>Header 3</h3>
<h4>Header 4</h4>
<h5>Header 5</h5>
<h6>Header 6</h6>


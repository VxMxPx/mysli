<?php

#: Before
use mysli\markdown;

#: Test Headers
$markdown = <<<MARKDOWN
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

MARKDOWN;

return assert::equals(markdown::process($markdown),
'<h1>Header 1</h1>
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
<h6>Header 6</h6>');

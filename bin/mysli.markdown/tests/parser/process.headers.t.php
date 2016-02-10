<?php

#: Before
use mysli\markdown;

# ------------------------------------------------------------------------------
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
'<h1 id="header-1">Header 1</h1>
<h2 id="header-2">Header 2</h2>
<h3 id="header-3">Header 3</h3>
<h4 id="header-4">Header 4</h4>
<h5 id="header-5">Header 5</h5>
<h6 id="header-6">Header 6</h6>
<h1 id="header-1-2">Header 1</h1>
<h2 id="header-2-2">Header 2</h2>
<h3 id="header-3-2">Header 3</h3>
<h4 id="header-4-2">Header 4</h4>
<h5 id="header-5-2">Header 5</h5>
<h6 id="header-6-2">Header 6</h6>
<h1 id="header-1-3">Header 1</h1>
<h2 id="header-2-3">Header 2</h2>
<h3 id="header-3-3">Header 3</h3>
<h4 id="header-4-3">Header 4</h4>
<h5 id="header-5-3">Header 5</h5>
<h6 id="header-6-3">Header 6</h6>');

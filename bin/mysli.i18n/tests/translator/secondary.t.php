<?php

#: Before
use mysli\i18n\translator;

#: Test Get
#: Expect String sl
$translator = new translator('en-us', 'sl');
return $translator->secondary();

#: Test Set/Get
#: Expect String en-us
$translator = new translator('en-us', 'sl');
$translator->secondary('en-us');
return $translator->primary();

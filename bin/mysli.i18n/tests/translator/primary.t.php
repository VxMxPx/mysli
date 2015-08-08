<?php

#: Before
use mysli\i18n\translator;

#: Test Get
#: Expect String en-us
$translator = new translator('en-us', null);
return $translator->primary();

#: Test Set/Get
#: Expect String sl
$translator = new translator('en-us', null);
$translator->primary('sl');
return $translator->primary();

<?php

#: Before
use mysli\i18n\translator;
use mysli\i18n\root\tests\translator\__data;

#: Define Translator
$translator = new translator('en-us', 'sl');
$translator->append(__data::$general);

#: Test Basic
#: Use Translator
#: Expect String "Hello World!"
return $translator->translate('HELLO_WORLD');

#: Test Multiline, keep lines
#: Use Translator
return assert::equals(
    $translator->translate('MULTILINE_KEEP_LINES'),
    "Hello,\nthe text will stay\nin multiple lines!"
);

#: Test Variable
#: Use Translator
#: Expect String "Hi there, Riki!"
return $translator->translate('GREETING', 'Riki');

#: Test Multiple Variables
#: Use Translator
#: Expect String "Hi there, Riki you're 2 years old."
return $translator->translate('GREETING_AND_AGE', ['Riki', 2]);

#: Test Variables with Placeholders
#: Use Translator
#: Expect String Hi there, please <a href="#login">login</a> or <a href="#register">register</a>.
return $translator->translate(
    'GREETING_AND_REGISTER',
    [
        '<a href="#login">%s</a>',
        '<a href="#register">%s</a>'
    ]
);

# --- Numeric ------------------------------------------------------------------

#: Test Numeric, No Number
#: Use Translator
#: Expect String "Comments"
return $translator->translate('COMMENTS');

#: Test Numeric, 0
#: Use Translator
#: Expect String "No comments."
return $translator->translate(['COMMENTS', 0]);

#: Test Numeric, 1
#: Use Translator
#: Expect String "One comment."
return $translator->translate(['COMMENTS', 1]);

#: Test Numeric, 2
#: Use Translator
#: Expect String "2 comments."
return $translator->translate(['COMMENTS', 2]);

#: Test Numeric, 3
#: Use Translator
#: Expect String "3 comments."
return $translator->translate(['COMMENTS', 3]);

#: Test Numeric, 50
#: Use Translator
#: Expect String "50 comments."
return $translator->translate(['COMMENTS', 50]);

# --- Numeric Special ----------------------------------------------------------

#: Test Numbers, 7
#: Use Translator
#: Expect String "I'm ending with 7!"
return $translator->translate(['NUMBERS', 7]);

#: Test Numbers, 107
#: Use Translator
#: Expect String "I'm ending with 7!"
return $translator->translate(['NUMBERS', 107]);

#: Test Numbers, 4
#: Use Translator
#: Expect String "I'm starting with 4!"
return $translator->translate(['NUMBERS', 4]);

#: Test Numbers, 400
#: Use Translator
#: Expect String "I'm starting with 4!"
return $translator->translate(['NUMBERS', 400]);

#: Test Numbers, 407
#: Use Translator
#: Expect String "I'm ending with 7!"
return $translator->translate(['NUMBERS', 407]);

#: Test Numbers, 10002
#: Use Translator
#: Expect String "I'm starting with 1 and ending with 2!"
return $translator->translate(['NUMBERS', 10002]);

#: Test Numbers, 12
#: Use Translator
#: Expect String "I'm starting with 1 and ending with 2!"
return $translator->translate(['NUMBERS', 12]);

# --- Odd/Even -----------------------------------------------------------------

#: Test Odd/Even, 7
#: Use Translator
#: Expect String "I'm odd! :S"
return $translator->translate(['ODD', 7]);

#: Test Odd/Even, 103
#: Use Translator
#: Expect String "I'm odd! :S"
return $translator->translate(['ODD', 103]);

#: Test Odd/Even, 4
#: Use Translator
#: Expect String "I'm even! :)"
return $translator->translate(['ODD', 4]);

#: Test Odd/Even, 400
#: Use Translator
#: Expect String "I'm even! :)"
return $translator->translate(['ODD', 400]);

#: Test Odd/Even, 10002
#: Use Translator
#: Expect String "I'm even! :)"
return $translator->translate(['ODD', 10002]);

#: Test Odd/Even, 1
#: Use Translator
#: Expect String "I'm odd! :S"
return $translator->translate(['ODD', 1]);

#: Test Odd/Even, 0
#: Use Translator
#: Expect String "I'm even! :)"
return $translator->translate(['ODD', 0]);

# --- Specific Number ----------------------------------------------------------

#: Test Specific, 2
#: Use Translator
#: Expect String "Two or nine!"
return $translator->translate(['TWO_AND_NINE', 2]);

#: Test Specific, 9
#: Use Translator
#: Expect String "Two or nine!"
return $translator->translate(['TWO_AND_NINE', 9]);

#: Test Specific, 34, Not Found
#: Use Translator
#: Expect Null
return $translator->translate(['TWO_AND_NINE', 34]);

#: Test Specific, 3242, Not Found
#: Use Translator
#: Expect Null
return $translator->translate(['TWO_AND_NINE', 3242]);

#: Test Specific, 9999, Not Found
#: Use Translator
#: Expect Null
return $translator->translate(['TWO_AND_NINE', 9999]);

# --- Range --------------------------------------------------------------------

#: Test Range, 0
#: Use Translator
#: Expect String "Hopes"
return $translator->translate(['AGE', 0]);

#: Test Range, 1
#: Use Translator
#: Expect String "Hopes"
return $translator->translate(['AGE', 1]);

#: Test Range, 2
#: Use Translator
#: Expect String "Will"
return $translator->translate(['AGE', 2]);

#: Test Range, 4
#: Use Translator
#: Expect String "Purpose"
return $translator->translate(['AGE', 4]);

#: Test Range, 8
#: Use Translator
#: Expect String "Competence"
return $translator->translate(['AGE', 8]);

#: Test Range, 15
#: Use Translator
#: Expect String "Fidelity"
return $translator->translate(['AGE', 15]);

#: Test Range, 22
#: Use Translator
#: Expect String "Love"
return $translator->translate(['AGE', 22]);

#: Test Range, 54
#: Use Translator
#: Expect String "Care"
return $translator->translate(['AGE', 54]);

#: Test Range, 65
#: Use Translator
#: Expect String "Wisdom"
return $translator->translate(['AGE', 65]);

#: Test Range, 90
#: Use Translator
#: Expect String "Wisdom"
return $translator->translate(['AGE', 90]);

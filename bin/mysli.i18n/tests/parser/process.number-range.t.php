<?php

#: Before
use mysli\i18n\parser;

#: Test Basic
$processed = parser::process(<<<'LANG'
# Number ranges
@AGE[0...1]   Hopes
@AGE[2...3]   Will
@AGE[4]       Purpose
@AGE[5...12]  Competence
@AGE[13...19] Fidelity
@AGE[20...39] Love
@AGE[40...64] Care
@AGE[65+]     Wisdom
LANG
);
unset($processed['.meta']);

return assert::equals(
    $processed,
    [
        'AGE' => [
            '0...1'   => [ 'value' => 'Hopes' ],
            '2...3'   => [ 'value' => 'Will' ],
            '4'       => [ 'value' => 'Purpose' ],
            '5...12'  => [ 'value' => 'Competence' ],
            '13...19' => [ 'value' => 'Fidelity' ],
            '20...39' => [ 'value' => 'Love' ],
            '40...64' => [ 'value' => 'Care' ],
            '65+'     => [ 'value' => 'Wisdom' ],
        ]
    ]
);

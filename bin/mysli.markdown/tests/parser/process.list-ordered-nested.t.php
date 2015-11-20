<?php

#: Before
use mysli\markdown;

#: Test List Ordered Nested
$markdown = <<<MARKDOWN
1. One
    1. Sub 1
    2. Sub 2
2. Two
    1. Sub 1
        1. Sub 2
            1. Sub 3
                1. Sub 4
                    1. Sub 5
3. Three
4. Four
MARKDOWN;

return assert::equals(markdown::process($markdown),
'<ol>
    <li>One
        <ol>
            <li>Sub 1</li>
            <li>Sub 2</li>
        </ol>
    </li>
    <li>Two
        <ol>
            <li>Sub 1
                <ol>
                    <li>Sub 2
                        <ol>
                            <li>Sub 3
                                <ol>
                                    <li>Sub 4
                                        <ol>
                                            <li>Sub 5</li>
                                        </ol>
                                    </li>
                                </ol>
                            </li>
                        </ol>
                    </li>
                </ol>
            </li>
        </ol>
    </li>
    <li>Three</li>
    <li>Four</li>
</ol>');

--TEST--
--DESCRIPTION--
--FILE--
<?php
use mysli\util\markdown;

echo markdown::process(<<<EOF
*   Lorem ipsum dolor sit amet, consectetuer adipiscing elit.
    Aliquam hendrerit mi posuere lectus. Vestibulum enim wisi,
    viverra nec, fringilla in, laoreet vitae, risus.
*   Donec sit amet nisl. Aliquam semper ipsum sit amet velit.
    Suspendisse id sem consectetuer libero luctus adipiscing.

*   Lorem ipsum dolor sit amet, consectetuer adipiscing elit.
Aliquam hendrerit mi posuere lectus. Vestibulum enim wisi,
viverra nec, fringilla in, laoreet vitae, risus.
*   Donec sit amet nisl. Aliquam semper ipsum sit amet velit.
Suspendisse id sem consectetuer libero luctus adipiscing.
EOF
);
?>
--EXPECT--
<ul>
    <li>
        <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit.
            Aliquam hendrerit mi posuere lectus. Vestibulum enim wisi,
            viverra nec, fringilla in, laoreet vitae, risus.</p>
    </li>
    <li>
        <p>Donec sit amet nisl. Aliquam semper ipsum sit amet velit.
            Suspendisse id sem consectetuer libero luctus adipiscing.</p>
    </li>
    <li>
        <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit.
            Aliquam hendrerit mi posuere lectus. Vestibulum enim wisi,
            viverra nec, fringilla in, laoreet vitae, risus.</p>
    </li>
    <li>
        <p>Donec sit amet nisl. Aliquam semper ipsum sit amet velit.
            Suspendisse id sem consectetuer libero luctus adipiscing.</p>
    </li>
</ul>

<?php

#: Before
use mysli\toolkit\html;

#: Test Basic Characters
#: Expect String &amp; &quot; &#039; &lt; &gt;
return html::entities_encode('& " \' < >');

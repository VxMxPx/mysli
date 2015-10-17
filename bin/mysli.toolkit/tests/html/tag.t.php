<?php

#: Before
use mysli\toolkit\html;

#: Test Basic Self Closed
#: Expect String <hr/>
return html::tag('hr');

#: Test Empty
#: Expect String <div></div>
return html::tag('div', [], '');

#: Test Content and Attributes
#: Expect String <a href="http://domain.tld">Link!</a>
return html::tag('a', ['href' => 'http://domain.tld'], 'Link!');

#: Test Multiple Attributes
#: Expect String <img src="http://" alt="" class="default"/>
return html::tag('img', ['src' => 'http://', 'alt' => '', 'class' => 'default']);

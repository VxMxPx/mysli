# cURL Examples

To make a simple request, static `get` or `post` methods can be used:

     curl::get('http://domain.tld/page');
     curl::post('http://domain.tld/', ['key' => 'value']);

To make a request with cookies, set `$cookie` to true:

     curl::get('http://domain.tld', true);

For advanced options, self can be instantiated:

     $curl = new curl($url);
     $curl->set_opt(CURLOPT_FOLLOWLOCATION, false);
     $curl->exec();

You can apply default, cookie, get or post options on constructed object:

     $curl = new curl($url);
     static::set_defaults($curl);
     $curl->set_opt(CURLOPT_FOLLOWLOCATION, false);
     static::set_cookie($curl);
     $curl->exec();

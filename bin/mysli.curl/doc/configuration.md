# cURL Configuration

**DEFAULT**

Default initial options used by each get / post request.

    array mysli.curl, default [
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_ENCODING       => '',
        CURLOPT_AUTOREFERER    => true,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_MAXREDIRS      => 8
    ]

**USER_AGENT**

Weather to acquire and use user agent from current user.

     boolean mysli.curl, agent_fetch, [true]

**COSTUME_AGENT**

A fallaback, if user's agent couldn't be fetched,
or agent_fetch is set to false.

     string mysli.curl, agent_costume, [
         Mozilla/5.0 (X11; Linux x86_64; rv:35.0) Gecko/20100101 Firefox/35.0
     ]

**COOKIE_FILENAME**

Cookie will be saved in `{tmppath}/mysli.curl/{cookie_filename}`

     string mysli.curl, cookie_filename [cookies.txt]

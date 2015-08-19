# Cookies

A simple Cookies management.

## Setting

A cookie can be set, by calling a static method `set`, for example:

    cookie::set('name', 'cookie-value', $expiration);

If you need to set an advanced options, like domain and path, then a new
instance of this class can be constructed:

    $cookie = new cookie('name');
    $cookie->set_path('/foo/');
    $cookie->set_expire(time() + 60*60*24*4);

    // Set cookie with a static method:
    cookie::set($cookie);

## Getting

A cookie can be acquired by calling a static method `get`, for example:

    cookie::get('name');

A default value can be provided if cookie was not found:

    cookie::get('name', 'default-value');

Get method, accepts cookie object with advanced options:

    $cookie = new cookie('name');
    $cookie->set_encrypt(true);
    $cookie->set_encrypt_key('foo/bar');
    $cookie->set_signature(true);
    $cookie->set_signature_key('foo/bar');

    try
    {
        $value = cookie::get($cookie);
    }
    catch(\Exception $e)
    {
        // Signature is invalid...
    }

## Removing

Cookie can be removed by calling a static method `delete`, for example:

    cookie::remove('name');

Path and domain can be send along:

    cookie::remove('name', '/foo/', 'blog.domain.tld');

## Configuration

**PREFIX**

Gives an unique prefix to all cookies set by this application.

    string mysli.toolkit, cookie.prefix ['']

**ENCRYPT**

Weather cookies should be encrypted.

    boolean mysli.toolkit, cookie.encrypt [false]

**ENCRYPT_KEY**

A unique encryption key, used if encrypt is set to true.

    string mysli.toolkit, cookie.encrypt_key [null]

**SIGN**

Weather cookies should be signed.

    boolean mysli.toolkit, cookie.sign [false]

**SIGN_KEY**

A unique sign key (salt), used if sign is set to true.

    string mysli.toolkit, cookie.sign_key [null]

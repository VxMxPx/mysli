<?php


# ------------------------------------------------------------------------------
#: Test Override Basic
#: Expect String mysli\mocker\fs\fs
autoloader::__t_reset();
autoloader::resolve_use('mysli\frontend\frontend', 'mysli.toolkit.fs.{ fs }');
autoloader::override(['mysli.toolkit.fs.fs' => 'mysli.mocker.fs.fs']);
return autoloader::get_override('mysli\toolkit\fs\fs', 'mysli\frontend\frontend');

# ------------------------------------------------------------------------------
#: Test Override Basic, Non-Specific Class
#: Expect String mysli\mocker\fs\fs
autoloader::__t_reset();
autoloader::resolve_use('mysli\frontend\frontend', 'mysli.toolkit.fs.{ fs }');
autoloader::override(['mysli.toolkit.fs.fs' => 'mysli.mocker.fs.fs']);
return autoloader::get_override('mysli\toolkit\fs\fs', 'mysli\frontend\something');

# ------------------------------------------------------------------------------
#: Test Override Basic, Specific Class
#: Expect String mysli\mocker\fs\fs
autoloader::__t_reset();
autoloader::resolve_use('mysli\frontend\frontend', 'mysli.toolkit.fs.{ fs }');
autoloader::override(['mysli.toolkit.fs.fs' => 'mysli.mocker.fs.fs'], 'mysli.frontend.frontend');
return autoloader::get_override('mysli\toolkit\fs\fs', 'mysli\frontend\frontend');

# ------------------------------------------------------------------------------
#: Test Override Basic, Specific Class, Fail
#: Expect String mysli\toolkit\fs\fs
autoloader::__t_reset();
autoloader::resolve_use('mysli\frontend\frontend', 'mysli.toolkit.fs.{ fs }');
autoloader::override(['mysli.toolkit.fs.fs' => 'mysli.mocker.fs.fs'], 'mysli.frontend.frontend');
return autoloader::get_override('mysli\toolkit\fs\fs', 'mysli\frontend\another');

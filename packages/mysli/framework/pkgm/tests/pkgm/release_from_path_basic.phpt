--TEST--
--DESCRIPTION--
Get package release from path - this must be full absolute path.
--FILE--
<?php
use mysli\framework\pkgm\pkgm;
use mysli\framework\fs\fs;
var_dump(pkgm::release_from_path(fs::pkgpath('mysli/framework/pkgm/src/pkgm.php')));
var_dump(pkgm::release_from_path(fs::pkgpath('mysli/framework/pkgm/src/script/pkgm.php')));
var_dump(pkgm::release_from_path(fs::pkgpath('mysli/framework/pkgm/file.php')));
var_dump(pkgm::release_from_path(fs::pkgpath('not/found')));
?>
--EXPECT--
string(20) "mysli/framework/pkgm"
string(20) "mysli/framework/pkgm"
string(20) "mysli/framework/pkgm"
bool(false)

--TEST--
--DESCRIPTION--
Get package namespace from path - this must be full absolute path.
--FILE--
<?php
use mysli\framework\pkgm\pkgm;
use mysli\framework\fs\fs;

var_dump(
    pkgm::namespace_from_path(
        fs::pkgpath('mysli/framework/pkgm/src/pkgm.php')));
var_dump(
    pkgm::namespace_from_path(
        fs::pkgpath('mysli/framework/pkgm')));
var_dump(
    pkgm::namespace_from_path(
        fs::pkgpath('mysli/framework/pkgm/src/script/pkgm.php')));
var_dump(
    pkgm::namespace_from_path(
        fs::pkgpath('mysli/framework/pkgm/src/sub/folder/src/file.php')));
var_dump(
    pkgm::namespace_from_path(
        fs::pkgpath('mysli/framework/pkgm/templates/post.tplp.html')));
var_dump(
    pkgm::namespace_from_path(
        fs::pkgpath('not/found/file.php')));

?>
--EXPECT--
string(25) "mysli\framework\pkgm\pkgm"
string(25) "mysli\framework\pkgm\pkgm"
string(32) "mysli\framework\pkgm\script\pkgm"
string(40) "mysli\framework\pkgm\sub\folder\src\file"
string(35) "mysli\framework\pkgm\templates\post"
bool(false)

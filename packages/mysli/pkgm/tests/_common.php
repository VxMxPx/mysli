<?php

if (!class_exists('\\Mysli\\Core\\Core')) {
    include(__DIR__.'/../../core/core.php');
    new \Mysli\Core\Core(
        realpath(__DIR__.'/dummy/private'),
        realpath(__DIR__.'/dummy/packages')
    );
    include(__DIR__.'/_generator.php');
    include(__DIR__.'/../exceptions/dependency.php');
    include(__DIR__.'/../exceptions/package.php');
    include(__DIR__.'/../util.php');
    include(__DIR__.'/../autoloader.php');
    include(__DIR__.'/../control.php');
    include(__DIR__.'/../cache.php');
    include(__DIR__.'/../registry.php');
    include(__DIR__.'/../factory.php');
    include(__DIR__.'/../pkgm.php');
}

<?php

use Symfony\Component\Dotenv\Dotenv;

date_default_timezone_set('UTC');

// PHPUnit's autoloader
if (!file_exists($phpUnitAutoloaderPath = __DIR__.'/../bin/.phpunit/phpunit/vendor/autoload.php')) {
    // @phpstan-ignore-next-line
    exit('PHPUnit is not installed. Please run `bin/phpunit --version` to install it');
}

$phpunitLoader = require $phpUnitAutoloaderPath;
// Don't register the PHPUnit autoloader before the normal autoloader to prevent weird issues
$phpunitLoader->unregister();
$phpunitLoader->register();

require __DIR__.'/../vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

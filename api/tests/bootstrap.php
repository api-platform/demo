<?php

declare(strict_types=1);

use Faker\Factory as FakerFactory;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Zenstruck\Foundry\Object\Instantiator;
use Zenstruck\Foundry\Test\UnitTestConfig;

require dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists(dirname(__DIR__) . '/config/bootstrap.php')) {
    require dirname(__DIR__) . '/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

// https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#non-kernel-tests
UnitTestConfig::configure(instantiator: (Instantiator::withoutConstructor())
    ->allowExtra()
    ->alwaysForce(), faker: FakerFactory::create('en_GB'));

// https://github.com/symfony/symfony/issues/53812#issuecomment-1962740145
set_exception_handler([new ErrorHandler(), 'handleException']);

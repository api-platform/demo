<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Concat\DirnameDirConcatStringToDirectStringPathRector;
use Rector\CodeQuality\Rector\Empty_\SimplifyEmptyCheckOnEmptyArrayRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;

$rector = RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/config',
        __DIR__ . '/migrations',
        __DIR__ . '/public',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withRootFiles()
    ->withPhpSets() // empty means use the version from composer.json
    ->withSets([
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::DEAD_CODE,
        SetList::INSTANCEOF,
        SetList::PRIVATIZATION,
        SetList::TYPE_DECLARATION,
    ])
    ->withAttributesSets()
    ->withComposerBased(doctrine: true, phpunit: true, symfony: true)
    ->withSkip([
        __DIR__ . '/config/bundles.php',
        SimplifyEmptyCheckOnEmptyArrayRector::class,
        // only hits Symfony recipe files (config/bootstrap.php, config/preload.php, public/index.php,
        // tests/bootstrap.php): rewriting them would conflict on every `composer recipes:update`
        DirnameDirConcatStringToDirectStringPathRector::class,
    ])
;

$kernelFilename = __DIR__ . '/var/cache/dev/App_KernelDevDebugContainer.xml';
if (is_file($kernelFilename)) {
    $rector->withSymfonyContainerXml($kernelFilename);
}

return $rector;

<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Empty_\SimplifyEmptyCheckOnEmptyArrayRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;
use Rector\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector;

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
        SetList::STRICT_BOOLEANS,
    ])
    ->withAttributesSets()
    ->withComposerBased(doctrine: true, phpunit: true)
    ->withSkip([
        __DIR__ . '/config/bundles.php',
        DisallowedEmptyRuleFixerRector::class,
        SimplifyEmptyCheckOnEmptyArrayRector::class,
    ])
;

$kernelFilename = __DIR__ . '/var/cache/dev/App_KernelDevDebugContainer.xml';
if (is_file($kernelFilename)) {
    $rector->withSymfonyContainerXml($kernelFilename);
}

return $rector;

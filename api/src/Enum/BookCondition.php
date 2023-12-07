<?php

declare(strict_types=1);

namespace App\Enum;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;

/**
 * A list of possible conditions for the item.
 *
 * @see https://schema.org/OfferItemCondition
 */
#[ApiResource(
    shortName: 'BookCondition',
    types: ['https://schema.org/OfferItemCondition'],
    operations: [
        new GetCollection(provider: BookCondition::class . '::getCases'),
        new Get(provider: BookCondition::class . '::getCase'),
    ],
)]
enum BookCondition: string
{
    use EnumApiResourceTrait;

    /** Indicates that the item is new. */
    case NewCondition = 'https://schema.org/NewCondition';

    /** Indicates that the item is refurbished. */
    case RefurbishedCondition = 'https://schema.org/RefurbishedCondition';

    /** Indicates that the item is damaged. */
    case DamagedCondition = 'https://schema.org/DamagedCondition';

    /** Indicates that the item is used. */
    case UsedCondition = 'https://schema.org/UsedCondition';
}

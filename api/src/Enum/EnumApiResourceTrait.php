<?php

declare(strict_types=1);

namespace App\Enum;

use ApiPlatform\Metadata\Operation;
use Symfony\Component\Serializer\Annotation\Groups;

trait EnumApiResourceTrait
{
    public function getId(): string
    {
        return $this->name;
    }

    #[Groups('Enum:read')]
    public function getValue(): string
    {
        return $this->value;
    }

    public static function getCases(): array
    {
        return self::cases();
    }

    public static function getCase(Operation $operation, array $uriVariables): ?static
    {
        $name = $uriVariables['id'] ?? null;

        return self::tryFrom($name);
    }
}

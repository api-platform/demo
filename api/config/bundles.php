<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class                      => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class                                => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class                        => ['all' => true],
    Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle::class             => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class                       => ['all' => true],
    ApiPlatform\Core\Bridge\Symfony\Bundle\ApiPlatformBundle::class            => ['all' => true],
    Nelmio\CorsBundle\NelmioCorsBundle::class                                  => ['all' => true],
    Nelmio\Alice\Bridge\Symfony\NelmioAliceBundle::class                       => ['dev' => true],
    Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle::class => ['dev' => true],
    Hautelook\AliceBundle\HautelookAliceBundle::class                          => ['dev' => true],
];

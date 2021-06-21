<?php

declare(strict_types=1);

namespace App\Serializer;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Only used to avoid a denormalization of the UploadedFile.
 */
final class UploadedFileDenormalizer implements DenormalizerInterface
{
    /**
     * {@inheritdoc}
     *
     * @param UploadedFile $data
     */
    public function denormalize($data, string $type, string $format = null, array $context = []): UploadedFile
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return $data instanceof UploadedFile;
    }
}

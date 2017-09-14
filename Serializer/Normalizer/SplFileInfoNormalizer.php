<?php

namespace Xe\Framework\Client\BaseClient\Serializer\Normalizer;

use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizes an {@see \SplFileInfo} object to a resource.
 */
class SplFileInfoNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if (!$object instanceof \SplFileInfo) {
            throw new InvalidArgumentException('The object must be an instance of "'.\SplFileInfo::class.'".');
        }

        return fopen($object->getRealPath(), 'r');
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof \SplFileInfo;
    }
}

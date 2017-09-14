<?php

namespace Xe\Framework\Client\BaseClient\Serializer\Encoder;

use Symfony\Component\Serializer\Encoder\EncoderInterface;

/**
 * Encodes query data.
 */
class MultipartEncoder implements EncoderInterface
{
    const FORMAT = 'multipart';

    /**
     * {@inheritdoc}
     */
    public function encode($data, $format, array $context = [])
    {
        $multipart = [];
        foreach ($data as $key => $value) {
            if (isset($context['prefix'])) {
                $key = is_numeric($key) ? "{$context['prefix']}[{$key}]" : "{$context['prefix']}.{$key}";
            }

            if (is_array($value) || (is_object($value) && !$value instanceof \SplFileObject)) {
                // Recursive call.
                $multipart = array_merge($multipart, $this->encode((array) $value, $format, ['prefix' => $key]));
            } else {
                $multipart[] = [
                    'name' => $key,
                    'contents' => $value,
                ];
            }
        }

        return $multipart;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsEncoding($format)
    {
        return self::FORMAT === $format;
    }
}

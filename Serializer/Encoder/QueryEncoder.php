<?php

namespace Xe\Framework\Client\BaseClient\Serializer\Encoder;

use Symfony\Component\Serializer\Encoder\EncoderInterface;

/**
 * Encodes query data.
 */
class QueryEncoder implements EncoderInterface
{
    const FORMAT = 'query';

    /**
     * {@inheritdoc}
     */
    public function encode($data, $format, array $context = [])
    {
        $query = [];
        foreach ($data as $key => $value) {
            if (isset($context['prefix'])) {
                $key = is_numeric($key) ? "{$context['prefix']}[{$key}]" : "{$context['prefix']}.{$key}";
            }

            if (is_array($value) || is_object($value)) {
                // Recursive call.
                $query = array_merge($query, $this->encode((array) $value, $format, ['prefix' => $key]));
            } else {
                $query[$key] = $value;
            }
        }

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsEncoding($format)
    {
        return self::FORMAT === $format;
    }
}

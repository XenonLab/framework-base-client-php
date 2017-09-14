<?php

namespace Xe\Framework\Client\BaseClient\Annotations;

use Doctrine\Common\Annotations\Annotation\Target;
use Symfony\Component\Serializer\Serializer;

/**
 * @Annotation
 * @Target("METHOD")
 */
abstract class AbstractSerializable
{
    /**
     * @var \Symfony\Component\Serializer\Serializer
     */
    protected $serializer;

    public function __construct(array $values, Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @return \Symfony\Component\Serializer\Serializer
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * @param \Symfony\Component\Serializer\Serializer $serializer
     */
    public function setSerializer(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }
}

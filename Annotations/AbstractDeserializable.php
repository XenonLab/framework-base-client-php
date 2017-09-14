<?php

namespace Xe\Framework\Client\BaseClient\Annotations;

use Doctrine\Common\Annotations\Annotation\Target;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

/**
 * @Annotation
 * @Target("METHOD")
 */
abstract class AbstractDeserializable
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $format;

    /**
     * @var string
     */
    protected $property;

    /**
     * @var string
     */
    protected $exception;

    /**
     * @var \Symfony\Component\Serializer\Serializer
     */
    protected $serializer;

    public function __construct(array $values, Serializer $serializer)
    {
        $this->type = isset($values['type']) ? $values['type'] : null;
        $this->format = isset($values['format']) ? $values['format'] : JsonEncoder::FORMAT;
        $this->property = isset($values['property']) ? $values['property'] : null;
        $this->exception = isset($values['exception']) ? $values['exception'] : null;

        if (isset($this->type) && isset($values['list']) && $values['list']) {
            $this->type = "{$this->type}[]";
        }

        $this->serializer = $serializer;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @param string $property
     */
    public function setProperty($property)
    {
        $this->property = $property;
    }

    /**
     * @return string
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @param string $exception
     */
    public function setException($exception)
    {
        $this->exception = $exception;
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

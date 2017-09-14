<?php

namespace Xe\Framework\Client\BaseClient\Aspects;

use Go\Aop\Aspect;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\Around;
use GuzzleHttp\Exception\BadResponseException;
use Psr\Http\Message\ResponseInterface;
use Xe\Framework\Client\BaseClient\Psr7\DeserializedResponse;

class DeserializableAspect implements Aspect
{
    /**
     * @Around("@execution(Xe\Framework\Client\BaseClient\Annotations\AbstractDeserializable)")
     */
    public function aroundDeserializable(MethodInvocation $invocation)
    {
        $deserializableAnnotation = $invocation->getMethod()->getAnnotation('Xe\Framework\Client\BaseClient\Annotations\AbstractDeserializable');
        $serializer = $deserializableAnnotation->getSerializer();
        $type = $deserializableAnnotation->getType();
        $format = $deserializableAnnotation->getFormat();
        $context = [];
        $property = $deserializableAnnotation->getProperty();
        $exception = $deserializableAnnotation->getException();

        try {
            $response = $invocation->proceed();
        } catch (BadResponseException $e) {
            if (!isset($exception)) {
                // No custom exception class specified. Forward the original exception.
                throw $e;
            }

            // Handle deserializing exceptions for status codes > 400.
            try {
                // Chain the original exception with the one in the finally block.
                throw $e;
            } finally {
                try {
                    // Try to deserialize the response into a custom exception.
                    $body = $e->getResponse()->getBody()->getContents();
                    $e->getResponse()->getBody()->rewind();

                    // Throwing an exception while one is already thrown correctly sets the previous exception on the newly thrown exception.
                    throw $this->deserialize($serializer, $body, $exception, $format, $context, $property);
                } catch (\Exception $e) {
                    if ($e instanceof $exception) {
                        // Deserialization succeeded.
                        throw $e;
                    }

                    // Do nothing. Couldn't deserialize the response. The original exception will still be thrown.
                }
            }
        }

        // Handle deserializing full responses.
        if ($response instanceof ResponseInterface) {
            $body = $response->getBody()->getContents();
            $response->getBody()->rewind();

            return new DeserializedResponse($response, $this->deserialize($serializer, $body, $type, $format, $context, $property));
        }

        if (!is_string($response)) {
            // Don't need to deserialize non-strings.
            return $response;
        }

        // Handle deserializing just the body (string).
        return $this->deserialize($serializer, $response, $type, $format, $context, $property);
    }

    protected function deserialize($serializer, $body, $type, $format, $context, $property)
    {
        if (!isset($type)) {
            throw new \InvalidArgumentException('The "type" parameter is required when deserializing.');
        }

        // Deserialize only a portion of the response if requested.
        if (isset($property)) {
            $body = $serializer->decode($body, $format, $context);

            if (isset($body[$property])) {
                $body[$property] = $serializer->deserialize($serializer->encode($body[$property], $format, $context), $type, $format, $context);
            }

            return $body;
        }

        // By default, deserialize the entire response.
        return $serializer->deserialize($body, $type, $format, $context);
    }
}

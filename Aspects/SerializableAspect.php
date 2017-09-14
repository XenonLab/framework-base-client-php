<?php

namespace Xe\Framework\Client\BaseClient\Aspects;

use Go\Aop\Aspect;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\Around;
use GuzzleHttp\RequestOptions;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Xe\Framework\Client\BaseClient\Serializer\Encoder\MultipartEncoder;
use Xe\Framework\Client\BaseClient\Serializer\Encoder\QueryEncoder;

class SerializableAspect implements Aspect
{
    /**
     * @Around("@execution(Xe\Framework\Client\BaseClient\Annotations\AbstractSerializable)")
     */
    public function aroundSerializable(MethodInvocation $invocation)
    {
        $serializableAnnotation = $invocation->getMethod()->getAnnotation('Xe\Framework\Client\BaseClient\Annotations\AbstractSerializable');
        $serializer = $serializableAnnotation->getSerializer();

        $optionsParameterIndex = null;
        foreach ($invocation->getMethod()->getParameters() as $key => $reflectionParameter) {
            if ($reflectionParameter->getName() == 'options') {
                $optionsParameterIndex = $key;
                break;
            }
        }

        $arguments = $invocation->getArguments();
        if (isset($optionsParameterIndex)) {
            if (isset($arguments[$optionsParameterIndex][RequestOptions::QUERY])) {
                $arguments[$optionsParameterIndex][RequestOptions::QUERY] = $serializer->serialize($arguments[$optionsParameterIndex][RequestOptions::QUERY], QueryEncoder::FORMAT);
            }

            if (isset($arguments[$optionsParameterIndex][RequestOptions::BODY])) {
                $arguments[$optionsParameterIndex][RequestOptions::BODY] = $serializer->serialize($arguments[$optionsParameterIndex][RequestOptions::BODY], JsonEncoder::FORMAT);
            }

            if (isset($arguments[$optionsParameterIndex][RequestOptions::FORM_PARAMS])) {
                $arguments[$optionsParameterIndex][RequestOptions::FORM_PARAMS] = $serializer->serialize($arguments[$optionsParameterIndex][RequestOptions::FORM_PARAMS], QueryEncoder::FORMAT);
            }

            if (isset($arguments[$optionsParameterIndex][RequestOptions::MULTIPART])) {
                $arguments[$optionsParameterIndex][RequestOptions::MULTIPART] = $serializer->serialize($arguments[$optionsParameterIndex][RequestOptions::MULTIPART], MultipartEncoder::FORMAT);
            }
        }

        return $invocation->getMethod()->invokeArgs($invocation->getThis(), $arguments);
    }
}

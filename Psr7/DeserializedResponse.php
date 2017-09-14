<?php

namespace Xe\Framework\Client\BaseClient\Psr7;

use Psr\Http\Message\ResponseInterface;

class DeserializedResponse implements \ArrayAccess
{
    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var array|object
     */
    protected $body;

    /**
     * @param ResponseInterface $response
     * @param array|object      $body
     */
    public function __construct(ResponseInterface $response, $body)
    {
        $this->response = $response;
        $this->body = $body;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return array|object
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Forward method calls to the deserialized response for backwards compatibility.
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->body, $name], $arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->body[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return isset($this->body[$offset]) ? $this->body[$offset] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->body[] = $value;
        } else {
            $this->body[$offset] = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->body[$offset]);
    }
}

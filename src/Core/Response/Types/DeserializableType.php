<?php

namespace CoreLib\Core\Response\Types;

use Closure;
use CoreLib\Core\Response\Context;
use Throwable;

class DeserializableType
{
    /**
     * @var callable|null
     */
    private $deserializerMethod;

    public function setDeserializerMethod(callable $deserializerMethod): void
    {
        $this->deserializerMethod = $deserializerMethod;
    }

    public function getFrom(Context $context)
    {
        if (is_null($this->deserializerMethod)) {
            return null;
        }
        try {
            return Closure::fromCallable($this->deserializerMethod)($context->getResponse()->getBody());
        } catch (Throwable $t) {
            throw $context->toApiException($t->getMessage());
        }
    }
}

<?php

namespace ClgsRu\LaravelRabbitMQ\Contracts;

use ClgsRu\LaravelRabbitMQ\Exception\MessageDeserializerException;

interface MessageDeserializer
{
    /**
     * @param ConsumerMessage $message
     * @return ConsumerMessage
     * @throws MessageDeserializerException
     */
    public function deserialize(ConsumerMessage $message): ConsumerMessage;
}
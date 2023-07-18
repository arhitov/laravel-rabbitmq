<?php

namespace Arhitov\LaravelRabbitMQ\Contracts;

use Arhitov\LaravelRabbitMQ\Exception\MessageDeserializerException;

interface MessageDeserializer
{
    /**
     * @param ConsumerMessage $message
     * @return ConsumerMessage
     * @throws MessageDeserializerException
     */
    public function deserialize(ConsumerMessage $message): ConsumerMessage;
}
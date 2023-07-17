<?php

namespace ClgsRu\LaravelRabbitMQ\Message\Deserializers;

use ClgsRu\LaravelRabbitMQ\Contracts\ConsumerMessage;
use ClgsRu\LaravelRabbitMQ\Contracts\MessageDeserializer;
use ClgsRu\LaravelRabbitMQ\Exception\MessageDeserializerException;
use JsonException;

class JsonDeserializer implements MessageDeserializer
{
    /**
     * @param ConsumerMessage $message
     * @return ConsumerMessage
     * @throws MessageDeserializerException
     */
    public function deserialize(ConsumerMessage $message): ConsumerMessage
    {
        try {
            $body = json_decode($message->getBodySerialize(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new MessageDeserializerException('JsonException : ' . $e->getMessage());
        }
        $message->setBody($body);
        return $message;
    }
}
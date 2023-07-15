<?php

namespace ClgsRu\LaravelRabbitMQ\Message\Deserializers;

use ClgsRu\LaravelRabbitMQ\Contracts\ConsumerMessage;
use ClgsRu\LaravelRabbitMQ\Contracts\MessageDeserializer;
use JsonException;

class JsonDeserializer implements MessageDeserializer
{
    /**
     * @param ConsumerMessage $message
     * @return ConsumerMessage
     * @throws JsonException
     */
    public function deserialize(ConsumerMessage $message): ConsumerMessage
    {
        $body = json_decode($message->getBodySerialize(), true, 512, JSON_THROW_ON_ERROR);
        $message->setBody($body);
        return $message;
    }
}
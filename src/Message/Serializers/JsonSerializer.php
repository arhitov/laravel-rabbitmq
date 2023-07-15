<?php

namespace ClgsRu\LaravelRabbitMQ\Message\Serializers;

use ClgsRu\LaravelRabbitMQ\Contracts\MessageSerializer;
use ClgsRu\LaravelRabbitMQ\Contracts\PublisherMessage;
use JsonException;

class JsonSerializer implements MessageSerializer
{
    /**
     * @param PublisherMessage $message
     * @return PublisherMessage
     * @throws JsonException
     */
    public function serialize(PublisherMessage $message): PublisherMessage
    {
        $body = json_encode($message->getBody(), JSON_THROW_ON_ERROR);
        $message->setBodySerialize($body);
        return $message;
    }
}
<?php

namespace ClgsRu\LaravelRabbitMQ\Message\Serializers;

use ClgsRu\LaravelRabbitMQ\Contracts\PublisherMessage;
use ClgsRu\LaravelRabbitMQ\Contracts\MessageSerializer;
use ClgsRu\LaravelRabbitMQ\Exception\MessageSerializerException;
use JsonException;

class JsonSerializer implements MessageSerializer
{
    /**
     * @param PublisherMessage $message
     * @return PublisherMessage
     * @throws MessageSerializerException
     */
    public function serialize(PublisherMessage $message): PublisherMessage
    {
        try {
            $body = json_encode($message->getBody(), JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new MessageSerializerException('JsonException: ' . $e->getMessage());
        }
        $message->setBodySerialize($body);
        return $message;
    }
}
<?php

namespace Arhitov\LaravelRabbitMQ\Message\Serializers;

use Arhitov\LaravelRabbitMQ\Contracts\PublisherMessage;
use Arhitov\LaravelRabbitMQ\Contracts\MessageSerializer;
use Arhitov\LaravelRabbitMQ\Exception\MessageSerializerException;
use Illuminate\Queue\SerializesModels;

class ModelSerializer implements MessageSerializer
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
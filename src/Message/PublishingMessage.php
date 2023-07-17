<?php

namespace ClgsRu\LaravelRabbitMQ\Message;

use ClgsRu\LaravelRabbitMQ\Contracts\PublisherMessage;
use ClgsRu\LaravelRabbitMQ\Contracts\MessageSerializer;
use ClgsRu\LaravelRabbitMQ\Exception\MessageException;
use ClgsRu\LaravelRabbitMQ\Exception\MessageSerializerException;
use Illuminate\Support\Facades\App;

class PublishingMessage extends AbstractMessage implements PublisherMessage
{
    /**
     * @param string $body
     * @throws MessageException
     */
    public function setBody($body): void
    {
        parent::setBody($body);
        try {
            (App::make(MessageSerializer::class))->serialize($this);
        } catch (MessageSerializerException $e) {
            throw new MessageException('MessageSerializerException : ' . $e->getMessage());
        }
    }
}
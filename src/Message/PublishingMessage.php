<?php

namespace Arhitov\LaravelRabbitMQ\Message;

use Arhitov\LaravelRabbitMQ\Contracts\PublisherMessage;
use Arhitov\LaravelRabbitMQ\Contracts\MessageSerializer;
use Arhitov\LaravelRabbitMQ\Exception\MessageException;
use Arhitov\LaravelRabbitMQ\Exception\MessageSerializerException;
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
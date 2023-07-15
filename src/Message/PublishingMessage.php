<?php

namespace ClgsRu\LaravelRabbitMQ\Message;

use ClgsRu\LaravelRabbitMQ\Contracts\PublisherMessage;
use ClgsRu\LaravelRabbitMQ\Contracts\MessageSerializer;
use Illuminate\Support\Facades\App;

class PublishingMessage extends AbstractMessage implements PublisherMessage
{
    /**
     * @param string $body
     */
    public function setBody($body): void
    {
        parent::setBody($body);
        (App::make(MessageSerializer::class))->serialize($this);
    }
}
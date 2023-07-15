<?php

namespace ClgsRu\LaravelRabbitMQ\Message;

use ClgsRu\LaravelRabbitMQ\Contracts\ConsumerMessage;
use ClgsRu\LaravelRabbitMQ\Contracts\MessageDeserializer;
use Illuminate\Support\Facades\App;

class ConsumedMessage extends AbstractMessage implements ConsumerMessage
{
    public function getBody()
    {
        (App::make(MessageDeserializer::class))->deserialize($this);
        return parent::getBody();
    }
}
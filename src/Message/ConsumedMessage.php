<?php

namespace ClgsRu\LaravelRabbitMQ\Message;

use ClgsRu\LaravelRabbitMQ\Contracts\ConsumerMessage;
use ClgsRu\LaravelRabbitMQ\Contracts\MessageDeserializer;
use ClgsRu\LaravelRabbitMQ\Exception\MessageException;
use ClgsRu\LaravelRabbitMQ\Exception\MessageDeserializerException;
use Illuminate\Support\Facades\App;

class ConsumedMessage extends AbstractMessage implements ConsumerMessage
{
    /**
     * @return mixed
     * @throws MessageException
     */
    public function getBody()
    {
        try {
            (App::make(MessageDeserializer::class))->deserialize($this);
        } catch (MessageDeserializerException $e) {
            throw new MessageException('MessageDeserializerException : ' . $e->getMessage());
        }

        return parent::getBody();
    }
}
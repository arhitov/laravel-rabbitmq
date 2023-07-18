<?php

namespace Arhitov\LaravelRabbitMQ\Message;

use Arhitov\LaravelRabbitMQ\Contracts\ConsumerMessage;
use Arhitov\LaravelRabbitMQ\Contracts\MessageDeserializer;
use Arhitov\LaravelRabbitMQ\Exception\MessageException;
use Arhitov\LaravelRabbitMQ\Exception\MessageDeserializerException;
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
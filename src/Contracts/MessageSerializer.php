<?php

namespace ClgsRu\LaravelRabbitMQ\Contracts;

use ClgsRu\LaravelRabbitMQ\Exception\MessageSerializerException;

interface MessageSerializer
{
    /**
     * @param PublisherMessage $message
     * @return PublisherMessage
     * @throws MessageSerializerException
     */
    public function serialize(PublisherMessage $message): PublisherMessage;
}
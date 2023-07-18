<?php

namespace Arhitov\LaravelRabbitMQ\Contracts;

use Arhitov\LaravelRabbitMQ\Exception\MessageSerializerException;

interface MessageSerializer
{
    /**
     * @param PublisherMessage $message
     * @return PublisherMessage
     * @throws MessageSerializerException
     */
    public function serialize(PublisherMessage $message): PublisherMessage;
}
<?php

namespace ClgsRu\LaravelRabbitMQ\Contracts;

interface MessageSerializer
{
    public function serialize(PublisherMessage $message): PublisherMessage;
}
<?php

namespace ClgsRu\LaravelRabbitMQ\Contracts;

interface MessageDeserializer
{
    public function deserialize(ConsumerMessage $message): ConsumerMessage;
}
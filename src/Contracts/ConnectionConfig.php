<?php

namespace ClgsRu\LaravelRabbitMQ\Contracts;

interface ConnectionConfig extends InterfaceBaseConfig
{
    public function getAttributesForStreamConnection(): array;
}
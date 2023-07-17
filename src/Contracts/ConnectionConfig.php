<?php

namespace ClgsRu\LaravelRabbitMQ\Contracts;

interface ConnectionConfig extends InterfaceConfig
{
    public function getAttributesForStreamConnection(): array;
}
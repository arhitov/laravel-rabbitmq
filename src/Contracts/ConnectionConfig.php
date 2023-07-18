<?php

namespace Arhitov\LaravelRabbitMQ\Contracts;

interface ConnectionConfig extends InterfaceConfig
{
    public function getAttributesForStreamConnection(): array;
}
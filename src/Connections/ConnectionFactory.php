<?php

namespace Arhitov\LaravelRabbitMQ\Connections;

abstract class ConnectionFactory
{
    public static function build(): Connection
    {
        return new Connection(new Config());
    }
}
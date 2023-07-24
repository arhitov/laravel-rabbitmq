<?php

namespace Arhitov\LaravelRabbitMQ\Connections;

abstract class ConnectionFactory
{
    /**
     * @return Connection
     */
    public static function build(): Connection
    {
        return new Connection(new Config());
    }
}
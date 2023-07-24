<?php

namespace Arhitov\LaravelRabbitMQ\Contracts;

use PhpAmqpLib\Channel\AMQPChannel;

interface Connection
{
    public function config(): ConnectionConfig;
    public function connect(): void;
    public function isConnected(): bool;
    public function reconnect(): void;
    public function channel(): AMQPChannel;
}
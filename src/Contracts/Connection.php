<?php

namespace Arhitov\LaravelRabbitMQ\Contracts;

use PhpAmqpLib\Channel\AMQPChannel;

interface Connection
{
    public function config(): ConnectionConfig;
    public function connect(): void;
    public function channel(): AMQPChannel;
}
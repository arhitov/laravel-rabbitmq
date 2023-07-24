<?php

namespace Arhitov\LaravelRabbitMQ\Tests\Feature;

use Arhitov\LaravelRabbitMQ\Tests\TestCase;
use Arhitov\LaravelRabbitMQ\Contracts\Connection as ContractsConnection;
use PhpAmqpLib\Channel\AMQPChannel;

class ConnectorTest extends TestCase
{
    public function testConnection(): void
    {
        $this->assertInstanceOf(ContractsConnection::class, $this->connection, 'Connection instance of ContractsConnection');
        $this->assertFalse($this->connection->isConnected(), 'Connection is not active');
        $this->connection->connect();
        $this->assertTrue($this->connection->isConnected(), 'Connection is active');
        $this->assertInstanceOf(AMQPChannel::class, $this->connection->channel(), 'Channel instance of AMQPChannel');
    }
}
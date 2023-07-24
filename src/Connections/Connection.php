<?php

namespace Arhitov\LaravelRabbitMQ\Connections;

use Arhitov\LaravelRabbitMQ\Contracts\Connection as ContractsConnection;
use Arhitov\LaravelRabbitMQ\Contracts\ConnectionConfig;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use Exception;

class Connection implements ContractsConnection
{
    protected ConnectionConfig $config;
    protected ?AMQPStreamConnection $connection = null;
    protected ?AMQPChannel $channel = null;

    /**
     * @param ConnectionConfig $config
     */
    public function __construct(ConnectionConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @return ConnectionConfig
     */
    public function config(): ConnectionConfig
    {
        return $this->config;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function connect(): void
    {
        if ($this->connection) {
            return;
        }

        $this->connection = new AMQPStreamConnection(...$this->config->getAttributesForStreamConnection());
        $this->channel = $this->connection->channel();
    }

    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        return ! is_null($this->connection) && $this->connection->isConnected();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function reconnect(): void
    {
        if (is_null($this->connection)) {
            return;
        }

        $this->channel->close();
        $this->connection->reconnect();
        $this->channel = $this->connection->channel();
    }

    /**
     * @return AMQPChannel
     * @throws Exception
     */
    public function channel(): AMQPChannel
    {
        if (! $this->connection) {
            $this->connect();
        }

        if (! $this->channel->is_open()) {
            $this->reconnect();
        }

        return $this->channel;
    }
}
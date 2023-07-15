<?php

namespace ClgsRu\LaravelRabbitMQ\Connections;

use ClgsRu\LaravelRabbitMQ\Contracts\Connection as ContractsConnection;
use ClgsRu\LaravelRabbitMQ\Contracts\ConnectionConfig;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use Exception;

class Connection implements ContractsConnection
{
    protected ConnectionConfig $config;
    protected ?AMQPStreamConnection $connection = null;
    protected ?AMQPChannel $channel = null;

    public function __construct(ConnectionConfig $config)
    {
        $this->config = $config;
    }

    public function config(): ConnectionConfig
    {
        return $this->config;
    }

    /**
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
     * @throws Exception
     */
    public function channel(): AMQPChannel
    {
        if (! $this->connection) {
            $this->connect();
        }

        return $this->channel;
    }
}
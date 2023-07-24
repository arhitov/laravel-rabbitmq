<?php

namespace Arhitov\LaravelRabbitMQ\Consumer;

use Arhitov\LaravelRabbitMQ\Contracts\Connection;
use Arhitov\LaravelRabbitMQ\Contracts\Consumer as ContractsConsumer;
use Arhitov\LaravelRabbitMQ\Exception\ConfigException;
use Arhitov\LaravelRabbitMQ\Exception\QueueException;
use Arhitov\LaravelRabbitMQ\Queue\Config as QueueConfig;
use Arhitov\LaravelRabbitMQ\Queue\Queue;
//use Arhitov\LaravelRabbitMQ\Contracts\ConsumerMessage;

class Consumer implements ContractsConsumer
{

    protected Connection $connect;
    protected Queue $queue;

    public function __construct(Connection $connect)
    {
        $this->connect = $connect;
    }

    public function connect(): void
    {
        $this->connect->connect();
    }

    public function isConnected(): bool
    {
        return $this->connect->isConnected();
    }

//    public function handle(ConsumerMessage $message): void
//    {
//        // TODO: Implement handle() method.
//    }

//    /**
//     * @throws Throwable
//     */
//    public function failed(string $message, string $topic, Throwable $exception): void
//    {
//        throw $exception;
//    }

    /**
     * @param string|QueueConfig|array $queue
     * @return Queue
     * @throws QueueException
     * @throws ConfigException
     */
    public function bindQueue($queue): Queue
    {
        if (is_string($queue)) {
            $queue_config = new QueueConfig($queue);
        } elseif ($queue instanceof QueueConfig) {
            $queue_config = $queue;
        } elseif (is_array($queue)) {
            $queue_config = new QueueConfig($queue);
        } else {
            throw new QueueException('Invalid queue type specified');
        }
        return new Queue($this->connect, $queue_config);
    }
}
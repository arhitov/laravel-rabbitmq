<?php

namespace Arhitov\LaravelRabbitMQ\Contracts;

use Arhitov\LaravelRabbitMQ\Exception\ConfigException;
use Arhitov\LaravelRabbitMQ\Exception\QueueException;
use Arhitov\LaravelRabbitMQ\Queue\Queue;
use Arhitov\LaravelRabbitMQ\Queue\Config as QueueConfig;

interface Consumer
{
    /**
     * @param Connection $connect
     */
    public function __construct(Connection $connect);

    /**
     * @return void
     */
    public function connect(): void;

    /**
     * @return bool
     */
    public function isConnected(): bool;

//    abstract public function handle(ConsumerMessage $message): void;

    /**
     * @param string|QueueConfig|array $queue
     * @return Queue
     * @throws ConfigException
     * @throws QueueException
     */
    public function bindQueue($queue): Queue;
}
<?php

namespace Arhitov\LaravelRabbitMQ\Contracts;

use Arhitov\LaravelRabbitMQ\Queue\Queue;

abstract class Consumer
{
    protected Connection $connect;
    protected Queue $queue;

    public function __construct(Connection $connect)
    {
        $this->connect = $connect;
    }

//    abstract public function handle(ConsumerMessage $message): void;

//    /**
//     * @throws Throwable
//     */
//    public function failed(string $message, string $topic, Throwable $exception): void
//    {
//        throw $exception;
//    }

    public function makeQueue(QueueConfig $config): Queue
    {
        return new Queue($this->connect->channel(), $config);
    }
}
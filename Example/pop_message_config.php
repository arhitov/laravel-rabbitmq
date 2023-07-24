<?php

use Arhitov\LaravelRabbitMQ\Contracts\Consumer as ContractsConsumer;
use Arhitov\LaravelRabbitMQ\Exception\Exception;
use Arhitov\LaravelRabbitMQ\Queue\Config as QueueConfig;
use Illuminate\Support\Facades\App;

/**
 * @var ContractsConsumer $consumer
 */
$consumer = App::make(ContractsConsumer::class);

try {
    $queue_config = new QueueConfig('queue_a');
    $queue = $consumer->bindQueue($queue_config);
    $message = $queue->pop();
    $payload = $message->getBody();
} catch (Exception $e) {

}

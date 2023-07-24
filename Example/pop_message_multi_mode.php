<?php

use Illuminate\Support\Facades\App;
use Arhitov\LaravelRabbitMQ\Contracts\Consumer as ContractsConsumer;
use Arhitov\LaravelRabbitMQ\Exception\Exception;
use Arhitov\LaravelRabbitMQ\Queue\Config as QueueConfig;

/**
 * @var ContractsConsumer $consumer
 */
$consumer = App::make(ContractsConsumer::class);
$config = include __DIR__ . '/../config/example_array_mapping.php';

try {
    $queue_config = new QueueConfig($config['queue']['queue_b']);
    $queue = $consumer->bindQueue($queue_config);
    $message = $queue->pop();
    $payload = $message->getBody();
} catch (Exception $e) {

}

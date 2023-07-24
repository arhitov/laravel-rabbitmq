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
    $config = include __DIR__ . '/../config/example_array_mapping.php';
    $queue_config = new QueueConfig($config['queue']['queue_b']);
    $queue = $consumer->bindQueue($queue_config);
    $queue->declare();
} catch (Exception $e) {

}

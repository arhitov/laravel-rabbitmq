<?php

use Arhitov\LaravelRabbitMQ\Contracts\Consumer as ContractsConsumer;
use Arhitov\LaravelRabbitMQ\Exception\Exception;
use Illuminate\Support\Facades\App;

/**
 * @var ContractsConsumer $consumer
 */
$consumer = App::make(ContractsConsumer::class);

try {
    $queue = $consumer->bindQueue('queue_a');
    $queue->declare();
} catch (Exception $e) {

}

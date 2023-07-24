<?php

use Arhitov\LaravelRabbitMQ\Exception\Exception;
use Arhitov\LaravelRabbitMQ\Contracts\Consumer as ContractsConsumer;
use Illuminate\Support\Facades\App;

/**
 * @var ContractsConsumer $consumer
 */
$consumer = App::make(ContractsConsumer::class);

try {
    $queue = $consumer->bindQueue('queue_a');
    $message = $queue->pop();
    var_dump($message ? $message->getBody() : null);
} catch (Exception $e) {

}

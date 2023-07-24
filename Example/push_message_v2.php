<?php

use Arhitov\LaravelRabbitMQ\Contracts\Publisher as ContractsPublisher;
use Arhitov\LaravelRabbitMQ\Exception\Exception;
use Arhitov\LaravelRabbitMQ\Message\PublishingMessage;
use Illuminate\Support\Facades\App;

/**
 * @var ContractsPublisher $publisher
 */
$publisher = App::make(ContractsPublisher::class);

try {
    $message = new PublishingMessage(
        'exchange_a',
        null,
        'payload',
        'create'
    );
    $publisher->push($message);
} catch (Exception $e) {

}

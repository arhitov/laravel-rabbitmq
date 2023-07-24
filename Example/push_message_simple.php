<?php

use Arhitov\LaravelRabbitMQ\Contracts\Publisher as ContractsPublisher;
use Arhitov\LaravelRabbitMQ\Exception\Exception;
use Illuminate\Support\Facades\App;

/**
 * @var ContractsPublisher $publisher
 */
$publisher = App::make(ContractsPublisher::class);

try {
    $publisher->createMessage(
        'exchange_a',
        null,
        'payload',
        'create'
    )->push();
} catch (Exception $e) {

}
<?php

use Arhitov\LaravelRabbitMQ\Exception\Exception;
use Arhitov\LaravelRabbitMQ\Mapping\Mapper;
use Illuminate\Support\Facades\App;

/**
 * @var Mapper $mapper
 */
$mapper = App::make(Mapper::class);

try {
    $exchange = $mapper->exchange(
        'exchange_a'
    );
    $exchange_background = $mapper->exchange(
        'exchange_b',
        'x-consistent-hash',
        false,
        true,
        true,
        ['hash-header' => 'uid']
    );

    $queue_moderation = $mapper->queue(
        'queue_a'
    );
    $queue_background = $mapper->queue(
        ['queue_%N%', 10] // [0, ..., 9]
    );

    $mapper->bind($exchange_background, $exchange, 'create');
    $mapper->bind($exchange_background, $exchange, 'edit');
    $mapper->bind($exchange_background, $exchange, 'on');
    $mapper->bind($exchange_background, $exchange, 'off');
    $mapper->bind($exchange_background, $exchange, 'del');

    $mapper->bind($queue_moderation, $exchange, 'create');
    $mapper->bind($queue_moderation, $exchange, 'edit');

    $mapper->bind($queue_background, $exchange_background, 2);

    $mapper->execute();

} catch (Exception $e) {

}

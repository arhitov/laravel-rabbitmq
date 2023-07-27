<?php

use Arhitov\LaravelRabbitMQ\Contracts\Consumer as ContractsConsumer;
use Arhitov\LaravelRabbitMQ\Exception\Exception;
use Arhitov\LaravelRabbitMQ\Exception\MessageException;
use Arhitov\LaravelRabbitMQ\Message\ConsumedMessage;
use Illuminate\Support\Facades\App;

/**
 * @var ContractsConsumer $consumer
 */
$consumer = App::make(ContractsConsumer::class);
try {
    $queue = $consumer->bindQueue('queue_a');
    $daemon = $queue->getDaemon(
        true // Confirmation of the execution of a message from the queue
    );
    $daemon->setOptions('timeout_wait_message', 10); // (seconds) Time to wait for a message before closing the connection

    $limit_message = 10;
    $daemon->subscribe(function (ConsumedMessage $message) use ($daemon, &$limit_message) {
        $limit_message -= 1;
        if ($limit_message < 0) {
            $daemon->stop();
        }
        try {
            $body = $message->getBody();
            dump($body);
        } catch (MessageException $e) {
            echo 'ERROR: ' . $e->getMessage();
            $daemon->stop();
        }
    });

    $daemon->start();

    echo 'JobsProcessed: ' . $daemon->getJobsProcessed() . PHP_EOL;

    echo (
        $daemon->getError()
            ? 'ERROR: ' . $daemon->getError()->getMessage()
            : 'Stopped'
        ) . PHP_EOL;

} catch (Exception $e) {
}

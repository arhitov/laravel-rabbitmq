<?php

namespace Arhitov\LaravelRabbitMQ\Contracts;

use Arhitov\LaravelRabbitMQ\Exception\PublisherException as PublisherException;
use Arhitov\LaravelRabbitMQ\Message\PublishingMessage;

interface Publisher
{
    public function __construct(Connection $connect);

    /**
     * @param string|null $exchange
     * @param string|null $queue
     * @param mixed $body
     * @param string|null $routing_key
     * @param int|null $timestamp
     * @return PublishingMessage
     */
    public function createMessage(
        ?string $exchange,
        ?string $queue,
                $body,
        ?string $routing_key = null,
        ?int $timestamp = null
    ): PublishingMessage;

    /**
     * @param PublishingMessage $message
     * @throws PublisherException
     */
    public function push(PublishingMessage $message): void;
}
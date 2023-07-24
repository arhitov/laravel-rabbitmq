<?php

namespace Arhitov\LaravelRabbitMQ\Contracts;

use Arhitov\LaravelRabbitMQ\Contracts\Publisher as ContractsPublisher;
use Arhitov\LaravelRabbitMQ\Exception\MessageException;

interface PublisherMessage extends Message
{
    /**
     * @param string $body
     * @throws MessageException
     */
    public function setBody($body): void;
    public function setHeaders(array $headers): void;
    public function bindPublisher(ContractsPublisher $publisher): void;
    public function push(): void;
}
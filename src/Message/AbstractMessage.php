<?php

namespace ClgsRu\LaravelRabbitMQ\Message;

use PhpAmqpLib\Message\AMQPMessage;
use ClgsRu\LaravelRabbitMQ\Contracts\Message;
use ClgsRu\LaravelRabbitMQ\Exception\FaitSetPropertyException;

abstract class AbstractMessage implements Message
{
    protected ?string $exchange;
    protected ?string $queue;
    protected ?string $routing_key;
    /**
     * @var mixed
     */
    protected $body;
    protected ?string $body_serialize = null;
    protected ?int $timestamp;
    protected array $headers = [];
    protected int $delivery_mode = AMQPMessage::DELIVERY_MODE_NON_PERSISTENT;

    /**
     * @param string|null $exchange
     * @param string|null $routing_key
     * @param mixed $body
     * @param int|null $timestamp
     */
    public function __construct(
        ?string $exchange,
        ?string $queue,
        ?string $routing_key,
        $body,
        ?int $timestamp = null
    ) {
        $this->exchange = $exchange;
        $this->queue = $queue;
        $this->routing_key = $routing_key;
        $this->setBody($body);
        $this->timestamp = $timestamp;
    }

    public function getExchange(): ?string
    {
        return $this->exchange;
    }

    public function getQueue(): ?string
    {
        return $this->queue;
    }

    public function getRoutingKey(): ?string
    {
        return $this->routing_key;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body): void
    {
        $this->body = $body;
    }

    public function getTimestamp(): ?int
    {
        return $this->timestamp;
    }

    public function hasBodySerialize(): bool
    {
        return ! is_null($this->body_serialize);
    }

    public function getBodySerialize(): string
    {
        return $this->body_serialize;
    }

    /**
     * @param string $body
     * @return void
     */
    public function setBodySerialize(string $body): void
    {
        $this->body_serialize = $body;
    }

    public function hasHeaders(): bool
    {
        return ! empty($this->headers);
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param int $mode
     * @return void
     * @throws FaitSetPropertyException
     */
    public function setDeliveryMode(int $mode): void
    {
        if (! in_array($mode, [
            AMQPMessage::DELIVERY_MODE_NON_PERSISTENT,
            AMQPMessage::DELIVERY_MODE_PERSISTENT
        ])) {
            throw new FaitSetPropertyException("Mode setting \"$mode\" is not supported");
        }
        $this->delivery_mode = $mode;
    }
}
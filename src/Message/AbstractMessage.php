<?php

namespace Arhitov\LaravelRabbitMQ\Message;

use Arhitov\LaravelRabbitMQ\Contracts\Message;
use Arhitov\LaravelRabbitMQ\Exception\FaitSetPropertyException;
use PhpAmqpLib\Message\AMQPMessage;

abstract class AbstractMessage implements Message
{
    protected ?string $exchange;
    protected ?string $queue;
    /**
     * @var mixed
     */
    protected $body;
    protected ?string $body_serialize = null;
    protected ?string $routing_key;
    protected ?int $timestamp;
    protected array $headers = [];
    protected int $delivery_mode = AMQPMessage::DELIVERY_MODE_NON_PERSISTENT;

    /**
     * @param string|null $exchange
     * @param string|null $queue
     * @param mixed $body
     * @param string|null $routing_key
     * @param int|null $timestamp
     */
    public function __construct(
        ?string $exchange,
        ?string $queue,
        $body,
        ?string $routing_key = '',
        ?int $timestamp = null
    ) {
        $this->exchange = $exchange;
        $this->queue = $queue;
        $this->setBody($body);
        $this->routing_key = $routing_key;
        $this->timestamp = $timestamp;
    }

    /**
     * @return string|null
     */
    public function getExchange(): ?string
    {
        return $this->exchange;
    }

    /**
     * @return string|null
     */
    public function getQueue(): ?string
    {
        return $this->queue;
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

    /**
     * @return string|null
     */
    public function getRoutingKey(): ?string
    {
        return $this->routing_key;
    }

    /**
     * @return int|null
     */
    public function getTimestamp(): ?int
    {
        return $this->timestamp;
    }

    /**
     * @return bool
     */
    public function hasBodySerialize(): bool
    {
        return ! is_null($this->body_serialize);
    }

    /**
     * @return string
     */
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

    /**
     * @return bool
     */
    public function hasHeaders(): bool
    {
        return ! empty($this->headers);
    }

    /**
     * @return array
     */
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
<?php

namespace Arhitov\LaravelRabbitMQ\Publisher;

use Arhitov\LaravelRabbitMQ\Contracts\Connection;
use Arhitov\LaravelRabbitMQ\Contracts\Publisher as ContractsPublisher;
use Arhitov\LaravelRabbitMQ\Exception\PublisherException;
use Arhitov\LaravelRabbitMQ\Message\PublishingMessage;
use PhpAmqpLib\Exception\AMQPExceptionInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class Publisher implements ContractsPublisher
{
    protected Connection $connect;

    public function __construct(Connection $connect)
    {
        $this->connect = $connect;
    }

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
    ): PublishingMessage {
        $message = new PublishingMessage($exchange, $queue, $body, $routing_key, $timestamp);
        $message->bindPublisher($this);
        return $message;
    }

    /**
     * @param PublishingMessage $message
     * @throws PublisherException
     */
    public function push(PublishingMessage $message): void
    {
        try {
            $AMQPMessage = new AMQPMessage($message->getBodySerialize());
            if ($message->hasHeaders()) {
                $AMQPMessage->set('application_headers', new AMQPTable($message->getHeaders()));
            }
            $AMQPMessage->set('timestamp', $message->getTimestamp() ?? time());

            $exchange = $message->getExchange();
            $this->connect->channel()->basic_publish(
                $AMQPMessage,
                $exchange ?? '',
                // Если exchange пустой, то routing_key является именем очереди
                (!empty($exchange))
                    ? ($message->getRoutingKey() ?? '')
                    : $message->getQueue()
            );
        } catch (AMQPExceptionInterface $e) {
            throw new PublisherException($e->getMessage());
        }
    }
}
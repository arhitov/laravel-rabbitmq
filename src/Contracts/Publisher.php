<?php

namespace ClgsRu\LaravelRabbitMQ\Contracts;

use ClgsRu\LaravelRabbitMQ\Message\PublishingMessage;
use ClgsRu\LaravelRabbitMQ\Exception\PublisherException;
use PhpAmqpLib\Exception\AMQPExceptionInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use App;

abstract class Publisher
{
    protected Connection $connect;

    public function __construct(Connection $connect)
    {
        $this->connect = $connect;
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
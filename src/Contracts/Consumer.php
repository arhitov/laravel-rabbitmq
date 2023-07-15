<?php

namespace ClgsRu\LaravelRabbitMQ\Contracts;

use ClgsRu\LaravelRabbitMQ\Exception\ConsumerException;
use ClgsRu\LaravelRabbitMQ\Exception\FaitSetPropertyException;
use ClgsRu\LaravelRabbitMQ\Message\ConsumedMessage;
use PhpAmqpLib\Exception\AMQPExceptionInterface;

abstract class Consumer
{
    protected Connection $connect;
    protected QueueConfig $queue_config;

    public function __construct(Connection $connect)
    {
        $this->connect = $connect;
    }

//    abstract public function handle(ConsumerMessage $message): void;

//    /**
//     * @throws Throwable
//     */
//    public function failed(string $message, string $topic, Throwable $exception): void
//    {
//        throw $exception;
//    }

    public function setQueue(QueueConfig $config)
    {
        $this->queue_config = $config;
    }

    /**
     * Извлекает последний (самый старый) элемент из очереди, уменьшая размер на одну запись.
     * @return ConsumerMessage
     * @throws ConsumerException
     */
    public function pop(): ?ConsumerMessage
    {
        try {
            $AMQPMessage = $this->connect->channel()->basic_get($this->queue_config->name, true);
            if (is_null($AMQPMessage)) {
                return null;
            }

            $consumedMessage = new ConsumedMessage(
                $AMQPMessage->getExchange(),
                $this->queue_config->name,
                $AMQPMessage->getRoutingKey(),
                null,
                $AMQPMessage->has('timestamp') ? $AMQPMessage->get('timestamp') : null, // Properties
            );

            $consumedMessage->setBodySerialize($AMQPMessage->getBody());

            if ($AMQPMessage->has('delivery_mode')) {
                $consumedMessage->setDeliveryMode($AMQPMessage->get('delivery_mode'));
            }

            return $consumedMessage;
        } catch (AMQPExceptionInterface|FaitSetPropertyException $e) {
            throw new ConsumerException($e->getMessage());
        }
    }
}
<?php

namespace Arhitov\LaravelRabbitMQ\Queue;

use Arhitov\LaravelRabbitMQ\Contracts\ConsumerMessage;
use Arhitov\LaravelRabbitMQ\Contracts\QueueConfig;
use Arhitov\LaravelRabbitMQ\Exception\QueueException;
use Arhitov\LaravelRabbitMQ\Exception\FaitSetPropertyException;
use Arhitov\LaravelRabbitMQ\Message\ConsumedMessage;
use PhpAmqpLib\Exception\AMQPExceptionInterface;
use PhpAmqpLib\Channel\AMQPChannel;

class Queue
{
    private AMQPChannel $channel;
    protected QueueConfig $config;

    public function __construct(AMQPChannel $connect, QueueConfig $config)
    {
        $this->channel = $connect;
        $this->config = $config;
    }

    /**
     * Извлекает последний (самый старый) элемент из очереди, уменьшая размер на одну запись.
     * @return ConsumerMessage
     * @throws QueueException
     */
    public function pop(): ?ConsumerMessage
    {
        try {
            $AMQPMessage = $this->channel->basic_get($this->config->name, true);
            if (is_null($AMQPMessage)) {
                return null;
            }

            $consumedMessage = new ConsumedMessage(
                $AMQPMessage->getExchange(),
                $this->config->name,
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
            throw new QueueException($e->getMessage());
        }
    }

    /**
     * Purge all messages in queue
     * @return void
     */
    public function purge()
    {
        $this->channel->queue_purge($this->config->name);
    }
}
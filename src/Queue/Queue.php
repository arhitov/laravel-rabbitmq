<?php

namespace Arhitov\LaravelRabbitMQ\Queue;

use Arhitov\LaravelRabbitMQ\Contracts\Connection;
use Arhitov\LaravelRabbitMQ\Contracts\ConsumerMessage;
use Arhitov\LaravelRabbitMQ\Exception\ConfigException;
use Arhitov\LaravelRabbitMQ\Exchange\Exchange;
use Arhitov\LaravelRabbitMQ\Exception\QueueException;
use Arhitov\LaravelRabbitMQ\Exception\FaitSetPropertyException;
use Arhitov\LaravelRabbitMQ\Mapping\Mapper;
use Arhitov\LaravelRabbitMQ\Message\ConsumedMessage;
use PhpAmqpLib\Exception\AMQPExceptionInterface;

class Queue
{
    private Connection $connect;
    protected Config $config;

    /**
     * @param Connection $connect
     * @param Config $config
     */
    public function __construct(Connection $connect, Config $config)
    {
        $this->connect = $connect;
        $this->config = $config;
    }

    /**
     * @throws ConfigException
     */
    public function getName(): string
    {
        return $this->config->getName();
    }

    /**
     * Извлекает последний (самый старый) элемент из очереди, уменьшая размер на одну запись.
     * В режиме MultiMode поиск сообщения будет происходить в каждой очереди по порядку
     * @return ConsumerMessage
     * @throws QueueException
     * @throws FaitSetPropertyException
     */
    public function pop(): ?ConsumerMessage
    {
        foreach ($this->config->getNameList() as $queue_name) {
            try {
                $AMQPMessage = $this->connect->channel()->basic_get($queue_name, true);
            } catch (AMQPExceptionInterface $e) {
                if (404 == $e->getCode()) {
                    continue;
                }
                throw new QueueException($e->getMessage());
            }
            if (is_null($AMQPMessage)) {
                continue;
            }

            $consumedMessage = new ConsumedMessage(
                $AMQPMessage->getExchange(),
                $queue_name,
                null,
                $AMQPMessage->getRoutingKey(),
                $AMQPMessage->has('timestamp') ? $AMQPMessage->get('timestamp') : null, // Properties
            );

            $consumedMessage->setBodySerialize($AMQPMessage->getBody());

            if ($AMQPMessage->has('delivery_mode')) {
                $consumedMessage->setDeliveryMode($AMQPMessage->get('delivery_mode'));
            }

            return $consumedMessage;
        }

        return null;
    }

    /**
     * Get queue information
     * @return array
     */
    public function getInfo(): array
    {
        $result = [];
        foreach ($this->config->getNameList() as $queue_name) {
            try {
                [, $messageCount, $consumerCount] = $this->connect->channel()->queue_declare(
                    $queue_name,
                    true
                );
                $result[$queue_name] = [
                    'success' => true,
                    'message_count' => $messageCount,
                    'consumer_count' => $consumerCount,
                ];
            } catch (AMQPExceptionInterface $e) {
                $result[$queue_name] = [
                    'success' => false,
                    'code' => $e->getCode(),
                    'error' => $e->getMessage()
                ];
            }
        }
        return $result;
    }

    /**
     * Declares queue, creates if needed
     * @return bool
     */
    public function declare(): bool
    {
        $result = true;
        foreach ($this->config->getNameList() as $queue_name) {
            try {
                $this->connect->channel()->queue_declare(
                    $queue_name,
                    false,
                    $this->config->isDurable(),
                    $this->config->isExclusive(),
                    $this->config->isAutoDelete(),
                    false,
                    Mapper::make_arguments($this->config->getArguments())
                );
            } catch (AMQPExceptionInterface $e) {
                // Skip all the mistakes
                $result = false;
            }
        }
        return $result;
    }

    /**
     * @param Exchange $exchange
     * @param string $routing_key
     * @param array $arguments
     * @return bool
     */
    public function binding(Exchange $exchange, string $routing_key = '', array $arguments = []): bool
    {
        $result = true;
        foreach ($this->config->getNameList() as $queue_name) {
            try {
                $this->connect->channel()->queue_bind(
                    $queue_name,
                    $exchange->getName(),
                    $routing_key,
                    false,
                    Mapper::make_arguments($arguments)
                );
            } catch (AMQPExceptionInterface $e) {
                // Skip all the mistakes
                $result = false;
            }
        }
        return $result;
    }

    /**
     * Purge all messages in queue
     * @return bool
     */
    public function purge(): bool
    {
        $result = true;
        foreach ($this->config->getNameList() as $queue_name) {
            try {
                $this->connect->channel()->queue_purge($queue_name);
            } catch (AMQPExceptionInterface $e) {
                // Skip all the mistakes
                $result = false;
            }
        }
        return $result;
    }

    /**
     * Deletes a queue
     * @return bool
     */
    public function delete(): bool
    {
        $result = true;
        foreach ($this->config->getNameList() as $queue_name) {
            try {
                $this->connect->channel()->queue_delete($queue_name);
            } catch (AMQPExceptionInterface $e) {
                // Skip all the mistakes
                $result = false;
            }
        }
        return $result;
    }
}
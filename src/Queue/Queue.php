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
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Support\Str;

class Queue
{
    protected Connection $connect;
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
     * @param string $queue_name
     * @return string
     */
    public function getConsumerTag(string $queue_name): string
    {
        $consumerTag = implode('_', [
            Str::slug(config('app.name', 'laravel')),
            md5(Str::random(16) . getmypid() . $queue_name),
        ]);

        return Str::substr($consumerTag, 0, 255);
    }

    /**
     * Unlike the "pop" method, the "daemon" method works in callback mode. This method does not load RabbitMQ by polling the queue every time. Instead, it subscribes to a queue.
     * Attention! The method does not have a termination point, you yourself need to worry about stopping it if necessary.
     * @param bool $message_confirm Confirmation of the execution of a message from the queue
     * @return Daemon
     */
    public function getDaemon(bool $message_confirm = null): Daemon
    {
        return new Daemon($this->connect, $this, $this->config, $message_confirm);
    }

    /**
     * Retrieves the last (oldest) element from the queue, decrementing the size by one entry.
     * In MultiMode mode, the search for a message will occur in each queue in order
     * This method makes a request when needed, i.e. at the time of the call, which creates a load on polling the presence of a message in the queue. To subscribe, use the subscription method
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

            return $this->makeConsumedMessage($queue_name, $AMQPMessage);
        }

        return null;
    }

    /**
     * @param string $queue_name
     * @param AMQPMessage $AMQPMessage
     * @return ConsumedMessage
     * @throws FaitSetPropertyException
     */
    public function makeConsumedMessage(string $queue_name, AMQPMessage $AMQPMessage): ConsumedMessage
    {
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

    /**
     * Queue exists
     * @return bool
     * @throws ConfigException
     * @throws QueueException
     */
    public function isExists(): bool
    {
        if (! $this->config->isSingleMode()) {
            throw new QueueException('Only allowed in SingleMode');
        }
        return $this->getInfoQueue($this->config->getName())['success'];
    }

    /**
     * Get queue information
     * @return array[]
     * @throws QueueException
     * @throws ConfigException
     */
    public function getInfo(): array
    {
        if (! $this->config->isSingleMode()) {
            throw new QueueException('Only allowed in SingleMode');
        }
        return $this->getInfoQueue($this->config->getName());
    }

    /**
     * Get queue information
     * @return array[]
     */
    public function getInfoList(): array
    {
        $result = [];
        foreach ($this->config->getNameList() as $queue_name) {
            $result[$queue_name] = $this->getInfoQueue($queue_name);
        }
        return $result;
    }

    /**
     * @param string $queue_name
     * @return array
     */
    protected function getInfoQueue(string $queue_name): array
    {
        try {
            [, $messageCount, $consumerCount] = $this->connect->channel()->queue_declare(
                $queue_name,
                true
            );
            return [
                'success' => true,
                'message_count' => $messageCount,
                'consumer_count' => $consumerCount,
            ];
        } catch (AMQPExceptionInterface $e) {
            return [
                'success' => false,
                'code' => $e->getCode(),
                'error' => $e->getMessage()
            ];
        }
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
<?php

namespace Arhitov\LaravelRabbitMQ\Exchange;

use Arhitov\LaravelRabbitMQ\Contracts\Connection;
use Arhitov\LaravelRabbitMQ\Exception\ExchangeException;
use Arhitov\LaravelRabbitMQ\Mapping\Mapper;
use Arhitov\LaravelRabbitMQ\Queue\Queue;
use PhpAmqpLib\Exception\AMQPExceptionInterface;

class Exchange
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
     * @return string
     */
    public function getName(): string
    {
        return $this->config->getName();
    }

    /**
     * Declares exchange, creates if needed
     * @return bool
     */
    public function declare(): bool
    {
        try {
            $this->connect->channel()->exchange_declare(
                $this->config->getName(),
                $this->config->getType(),
                false,
                $this->config->isDurable(),
                $this->config->isAutoDelete(),
                $this->config->isInternal(),
                false,
                Mapper::make_arguments($this->config->getArguments())
            );
            return true;
        } catch (AMQPExceptionInterface $e) {
            // Skip all the mistakes
            return false;
        }
    }

    /**
     * @param Exchange|Queue $destination
     * @param string $routing_key
     * @param array $arguments
     * @return bool
     * @throws ExchangeException
     */
    public function binding($destination, string $routing_key = '', array $arguments = []): bool
    {
        if ($destination instanceof Exchange) {
            try {
                $this->connect->channel()->exchange_bind(
                    $this->getName(),
                    $destination->getName(),
                    $routing_key,
                    false,
                    Mapper::make_arguments($arguments)
                );
                return true;
            } catch (AMQPExceptionInterface $e) {
                return false;
            }
        } elseif ($destination instanceof Queue) {
            return $destination->binding(
                $this,
                $routing_key,
                $arguments
            );
        } else {
            throw new ExchangeException('Not allowed type destination');
        }
    }

    /**
     * Deletes a queue
     * @return bool
     */
    public function delete(): bool
    {
        try {
            $this->connect->channel()->exchange_delete($this->config->getName());
            return true;
        } catch (AMQPExceptionInterface $e) {
            return false;
        }
    }
}
<?php

namespace Arhitov\LaravelRabbitMQ\Mapping;

use Arhitov\LaravelRabbitMQ\Contracts\Connection;
use Arhitov\LaravelRabbitMQ\Exception\ExchangeException;
use Arhitov\LaravelRabbitMQ\Exception\ConfigException;
use Arhitov\LaravelRabbitMQ\Exchange\Config as ExchangeConfig;
use Arhitov\LaravelRabbitMQ\Exchange\Exchange;
use Arhitov\LaravelRabbitMQ\Queue\Config as QueueConfig;
use Arhitov\LaravelRabbitMQ\Queue\Queue;
use PhpAmqpLib\Wire\AMQPTable;

class Mapper
{
    protected Connection $connect;

    /**
     * @var Exchange[]
     */
    protected array $exchange = [];

    /**
     * @var Queue[]
     */
    protected array $queue = [];

    /**
     * @var array[]
     */
    protected array $bind = [];

    /**
     * @param Connection $connect
     */
    public function __construct(Connection $connect)
    {
        $this->connect = $connect;
    }

    /**
     * @param array $config
     * @return void
     * @throws ExchangeException
     * @throws ConfigException
     * @example An example of displaying an array in the file config/example_array_mapping.php
     */
    public function loadConfig(array $config)
    {
        $list = [];
        foreach ($config['exchange'] ?? [] as $name => $exchange) {
            $list['exchange.' . $name] = $this->exchange($exchange + ['name' => $name]);
        }
        foreach ($config['queue'] ?? [] as $name => $queue) {
            $list['queue.' . $name] = $this->queue($queue + ['name' => $name]);
        }

        foreach ($config['bind'] ?? [] as $bind) {
            $this->bind($list[$bind[1]], $list[$bind[0]], $bind[2] ?? '', $bind[3] ?? []);
        }
    }

    /**
     * @param string|array $name
     * @param string|null $type
     * @param bool|null $auto_delete
     * @param bool|null $durable
     * @param bool|null $internal
     * @param array|null $arguments
     * @return Exchange
     * @throws ExchangeException
     * @see ExchangeConfig::__construct() Property as in Config constructor
     */
    public function exchange(
        $name,
        ?string $type = null,
        ?bool $auto_delete = null,
        ?bool $durable = null,
        ?bool $internal = null,
        ?array $arguments = null
    ): Exchange {
        $exchange_config = new ExchangeConfig($name, $type, $auto_delete, $durable, $internal, $arguments);
        $exchange = new Exchange($this->connect, $exchange_config);
        $this->exchange[] = $exchange;
        return $exchange;
    }

    /**
     * @return Exchange[]
     */
    public function getExchangeList(): array
    {
        return $this->exchange;
    }

    /**
     * @param string|array $name
     * @param bool|null $auto_delete
     * @param bool|null $durable
     * @param bool|null $exclusive
     * @param array|null $arguments
     * @return Queue
     * @throws ConfigException
     * @see QueueConfig::__construct() Property as in Config constructor
     */
    public function queue(
        $name,
        ?bool $auto_delete = null,
        ?bool $durable = null,
        ?bool $exclusive = null,
        ?array $arguments = null
    ): Queue {
        $queue_config = new QueueConfig($name, $auto_delete, $durable, $exclusive, $arguments);
        $queue = new Queue($this->connect, $queue_config);
        $this->queue[] = $queue;
        return $queue;
    }

    /**
     * @return Queue[]
     */
    public function getQueueList(): array
    {
        return $this->queue;
    }

    /**
     * @param $source
     * @param $destination
     * @param string $routing_key
     * @param array $arguments
     * @return void
     */
    public function bind($source, $destination, string $routing_key = '', array $arguments = [])
    {
        $this->bind[] = ['source' => $source, 'destination' => $destination, 'routing_key' => $routing_key, 'arguments' => $arguments];
    }

    /**
     * @param bool $delete_first Delete before creating
     * @return void
     */
    public function execute(bool $delete_first = false)
    {
        foreach ($this->exchange as $exchange) {
            if ($delete_first) {
                $exchange->delete();
            }
            $exchange->declare();
        }

        foreach ($this->queue as $queue) {
            if ($delete_first) {
                $queue->delete();
            }
            $queue->declare();
        }

        foreach ($this->bind as $bind) {
            $bind['source']->binding($bind['destination'], $bind['routing_key'], $bind['arguments']);
        }
    }

    /**
     * @param array $arguments
     * @return array|AMQPTable
     */
    public static function make_arguments(array $arguments)
    {
        return empty($arguments)
            ? []
            : (function($argument_list) {
                $arguments = new AMQPTable();
                foreach ($argument_list as $key => $val) {
                    $arguments->set($key, $val);
                }
                return $arguments;
            })($arguments);
    }
}
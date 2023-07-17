<?php

namespace ClgsRu\LaravelRabbitMQ\Mapping;

use ClgsRu\LaravelRabbitMQ\Contracts\Connection;
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

    public function __construct(Connection $connect)
    {
        $this->connect = $connect;
    }

    /**
     * @example An example of displaying an array in the file config/example_array_mapping.php
     * @param array $config
     * @return void
     */
    public function loadConfig(array $config)
    {
        $list = [];
        foreach ($config['exchange'] ?? [] as $name => $exchange) {
            $list['exchange.' . $name] = $this->exchange(
                $exchange['name'] ?? $name,
                $exchange['type'] ?? null,
                $exchange['auto_delete'] ?? null,
                $exchange['durable'] ?? null,
                $exchange['internal'] ?? null,
                $exchange['arguments'] ?? null
            );
        }
        foreach ($config['queue'] ?? [] as $name => $queue) {
            $list['queue.' . $name] = $this->queue(
                $queue['name'] ?? $name,
                $queue['auto_delete'] ?? null,
                $queue['durable'] ?? null,
                $queue['exclusive'] ?? null,
                $queue['arguments'] ?? null
            );
        }

        foreach ($config['bind'] ?? [] as $bind) {
            $list[$bind[0]]->bind($list[$bind[1]], $bind[2] ?? '', $bind[3] ?? []);
        }
    }

    /**
     * @param string $name
     * @param string|null $type
     * @param bool|null $auto_delete
     * @param bool|null $durable
     * @param bool|null $internal
     * @param array|null $arguments
     * @return Exchange
     */
    public function exchange(
        string $name,
        ?string $type = null,
        ?bool $auto_delete = null,
        ?bool $durable = null,
        ?bool $internal = null,
        ?array $arguments = null
    ): Exchange {
        $exchange = new Exchange($name, $type, $auto_delete, $durable, $internal, $arguments);
        $this->exchange[] = $exchange;
        return $exchange;
    }

    /**
     * @param string|array $name
     * @param bool|null $auto_delete
     * @param bool|null $durable
     * @param bool|null $exclusive
     * @param array|null $arguments
     * @return Queue
     */
    public function queue(
        $name,
        bool $auto_delete = null,
        bool $durable = null,
        bool $exclusive = null,
        array $arguments = null
    ): Queue {
        $queue = new Queue($name, $auto_delete, $durable, $exclusive, $arguments);
        $this->queue[] = $queue;
        return $queue;
    }

    /**
     * @param bool $delete_first Delete before creating
     * @return void
     */
    public function execute(bool $delete_first = false)
    {
        $channel = $this->connect->channel();

        foreach ($this->exchange as $exchange) {
            if ($delete_first) {
                $exchange->delete($channel);
            }
            $exchange->execute($channel);
        }

        foreach ($this->queue as $queue) {
            if ($delete_first) {
                $queue->delete($channel);
            }
            $queue->execute($channel);
        }

        foreach ($this->exchange as $exchange) {
            if ($exchange->isBindings()) {
                $exchange->binding($channel);
            }
        }

        foreach ($this->queue as $queue) {
            if ($queue->isBindings()) {
                $queue->binding($channel);
            }
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
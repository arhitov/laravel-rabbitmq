<?php

namespace Arhitov\LaravelRabbitMQ\Mapping;

use PhpAmqpLib\Channel\AMQPChannel;

class Exchange
{
    protected string $name;
    protected string $type = 'direct';
    protected bool $auto_delete = false;
    protected bool $durable = true;
    protected bool $internal = false;
    protected array $arguments = [];
    protected array $binds = [];

    /**
     * @param string $name
     * @param string|null $type
     * @param bool|null $auto_delete
     * @param bool|null $durable
     * @param bool|null $internal
     * @param array|null $arguments
     */
    public function __construct(
        string $name,
        ?string $type = null,
        ?bool $auto_delete = null,
        ?bool $durable = null,
        ?bool $internal = null,
        ?array $arguments = null
    ) {
        $this->name = $name;
        $this->type = $type ?? $this->type;
        $this->auto_delete = $auto_delete ?? $this->auto_delete;
        $this->durable = $durable ?? $this->durable;
        $this->internal = $internal ?? $this->internal;
        $this->arguments = $arguments ?? $this->arguments;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param Queue|Exchange $destination
     * @param string $routing_key
     * @param array $arguments
     * @return void
     */
    public function bind($destination, string $routing_key, array $arguments = [])
    {
        $this->binds[] = ['destination' => $destination, 'routing_key' => $routing_key, 'arguments' => $arguments];
    }

    /**
     * @return bool
     */
    public function isBindings(): bool
    {
        return ! empty($this->binds);
    }

    public function binding(AMQPChannel $channel)
    {
        $queue_bind = [];
        foreach ($this->binds as $bind) {
            if ($bind['destination'] instanceof Exchange) {
                $channel->exchange_bind(
                    $this->name,
                    $bind['destination']->getName(),
                    $bind['routing_key'],
                    false,
                    Mapper::make_arguments($bind['arguments']),
                    null
                );
            } else {
                $bind['destination']->bind($this, $bind['routing_key'], $bind['arguments']);
                $queue_name = $bind['destination']->getName();
                if (! array_key_exists($queue_name, $queue_bind)) {
                    $queue_bind[$queue_name] = $bind['destination'];
                }
            }
        }
        foreach ($queue_bind as $queue) {
            $queue->binding($channel);
        }
    }

    public function delete(AMQPChannel $channel): void
    {
        $channel->exchange_delete($this->name);
    }

    public function execute(AMQPChannel $channel): void
    {
        $channel->exchange_declare(
            $this->name,
            $this->type,
            false, // Грубо: по возможности использовать существующий "коннект"
            $this->durable,
            $this->auto_delete,
            $this->internal,
            false,
            Mapper::make_arguments($this->arguments),
            null
        );
    }
}
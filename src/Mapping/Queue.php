<?php

namespace Arhitov\LaravelRabbitMQ\Mapping;

use PhpAmqpLib\Channel\AMQPChannel;

class Queue
{
    /**
     * @var array|string
     */
    protected $name;
    protected array $names;
    protected bool $auto_delete = false;
    protected bool $durable = true;
    protected bool $exclusive = false;
    protected array $arguments = [];
    protected array $binds = [];

    /**
     * @param string|array $name
     * @param bool|null $exclusive
     * @param bool|null $auto_delete
     * @param bool|null $durable
     * @param array|null $arguments
     */
    public function __construct(
        $name,
        ?bool $auto_delete = false,
        ?bool $durable = null,
        ?bool $exclusive = null,
        ?array $arguments = null
    ) {
        $this->name = $name;
        $this->auto_delete = $auto_delete ?? $this->auto_delete;
        $this->durable = $durable ?? $this->durable;
        $this->exclusive = $exclusive ?? $this->exclusive;
        $this->arguments = $arguments ?? $this->arguments;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNameList(): array
    {
        $names = [];
        if (is_array($this->name)) {
            for ($i = 0; $i < $this->name[1]; ++$i) {
                $names[] = str_replace('%N%', $i, $this->name[0]);
            }
        } else {
            $names[] = $this->name;
        }
        return $names;
    }

    /**
     * @param Exchange $destination
     * @param string $routing_key
     * @param array $arguments
     * @return void
     */
    public function bind(Exchange $destination, string $routing_key, array $arguments = [])
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
        foreach ($this->getNameList() as $name) {
            foreach ($this->binds as $bind) {
                $channel->queue_bind(
                    $name,
                    $bind['destination']->getName(),
                    $bind['routing_key'],
                    false,
                    Mapper::make_arguments($bind['arguments']),
                    null
                );
            }
        }
    }

    public function delete(AMQPChannel $channel): void
    {
        foreach ($this->getNameList() as $name) {
            $channel->queue_delete($name);
        }
    }

    public function execute(AMQPChannel $channel): void
    {
        foreach ($this->getNameList() as $name) {
            $channel->queue_declare(
                $name,
                false, // Грубо: по возможности использовать существующий "коннект"
                $this->durable,
                $this->exclusive,
                $this->auto_delete,
                false,
                Mapper::make_arguments($this->arguments),
                null
            );
        }
    }
}
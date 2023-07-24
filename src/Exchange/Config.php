<?php

namespace Arhitov\LaravelRabbitMQ\Exchange;

use Arhitov\LaravelRabbitMQ\Contracts\InterfaceConfig;
use Arhitov\LaravelRabbitMQ\Exception\ExchangeException;

class Config implements InterfaceConfig
{
    protected string $name;
    protected string $type = 'direct';
    protected bool $auto_delete = false;
    protected bool $durable = true;
    protected bool $internal = false;
    protected array $arguments = [];

    /**
     * @param string|array $name
     * @param string|null $type
     * @param bool|null $auto_delete
     * @param bool|null $durable
     * @param bool|null $internal
     * @param array|null $arguments
     * @throws ExchangeException
     */
    public function __construct(
        $name,
        ?string $type = null,
        ?bool $auto_delete = null,
        ?bool $durable = null,
        ?bool $internal = null,
        ?array $arguments = null
    ) {
        if (is_string($name)) {
            $this->name = $name;
        } elseif (is_array($name)) {
            $this->name = $name['name'];
            $this->type = $name['type'] ?? $this->type;
            $this->auto_delete = $name['auto_delete'] ?? $this->auto_delete;
            $this->durable = $name['durable'] ?? $this->durable;
            $this->internal = $name['internal'] ?? $this->internal;
            $this->arguments = $name['arguments'] ?? $this->arguments;
        } else {
            throw new ExchangeException('The specification of the exchange is incorrectly indicated');
        }
        $this->type = $type ?? $this->type;
        $this->auto_delete = $auto_delete ?? $this->auto_delete;
        $this->durable = $durable ?? $this->durable;
        $this->internal = $internal ?? $this->internal;
        $this->arguments = $arguments ?? $this->arguments;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isAutoDelete(): bool
    {
        return $this->auto_delete;
    }

    /**
     * @return bool
     */
    public function isDurable(): bool
    {
        return $this->durable;
    }

    /**
     * @return bool
     */
    public function isInternal(): bool
    {
        return $this->internal;
    }

    /**
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }
}
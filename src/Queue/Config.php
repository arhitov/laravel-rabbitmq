<?php

namespace Arhitov\LaravelRabbitMQ\Queue;

use Arhitov\LaravelRabbitMQ\Contracts\InterfaceConfig;
use Arhitov\LaravelRabbitMQ\Exception\ConfigException;

/**
 * @property string $name
 */
class Config implements InterfaceConfig
{
    /**
     * @var string|array
     */
    protected $name;
    protected bool $auto_delete = false;
    protected bool $durable = true;
    protected bool $exclusive = false;
    protected array $arguments = [];

    /**
     * @param string|array $name
     * @param bool|null $auto_delete
     * @param bool|null $durable
     * @param bool|null $exclusive
     * @param array|null $arguments
     * @throws ConfigException
     */
    public function __construct(
        $name,
        ?bool $auto_delete = null,
        ?bool $durable = null,
        ?bool $exclusive = null,
        ?array $arguments = null
    ) {
        if (is_string($name)) {
            $this->name = $name;
        } elseif (is_array($name)) {
            if (array_key_exists('name', $name)) {
                $this->name = $name['name'];
                $this->auto_delete = $name['auto_delete'] ?? $this->auto_delete;
                $this->durable = $name['durable'] ?? $this->durable;
                $this->exclusive = $name['exclusive'] ?? $this->exclusive;
                $this->arguments = $name['arguments'] ?? $this->arguments;
            } else {
                $this->name = $name;
            }
        } else {
            throw new ConfigException('The specification of the queue is incorrectly indicated');
        }
        $this->auto_delete = $auto_delete ?? $this->auto_delete;
        $this->durable = $durable ?? $this->durable;
        $this->exclusive = $exclusive ?? $this->exclusive;
        $this->arguments = $arguments ?? $this->arguments;
    }

    /**
     * @return bool
     */
    public function isSingleMode(): bool
    {
        return is_string($this->name);
    }

    /**
     * @return bool
     */
    public function isMultiMode(): bool
    {
        return is_array($this->name);
    }

    /**
     * @return string
     * @throws ConfigException
     */
    public function getName(): string
    {
        if (is_array($this->name)) {
            throw new ConfigException('You cannot get the name of the queue in the multi mode');
        }
        return $this->name;
    }

    /**
     * @return array
     */
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
    public function isExclusive(): bool
    {
        return $this->exclusive;
    }

    /**
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }
}
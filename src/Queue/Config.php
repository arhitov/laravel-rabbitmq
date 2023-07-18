<?php

namespace Arhitov\LaravelRabbitMQ\Queue;

use Arhitov\LaravelRabbitMQ\Contracts\QueueConfig;
use Arhitov\LaravelRabbitMQ\Contracts\BaseConfig;

/**
 * @property string $name
 */
class Config extends BaseConfig implements QueueConfig
{
    const LIST_ALLOWED_PROPERTY_FOR_GET = ['name'];
    protected string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
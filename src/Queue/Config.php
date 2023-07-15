<?php

namespace ClgsRu\LaravelRabbitMQ\Queue;

use ClgsRu\LaravelRabbitMQ\Contracts\QueueConfig;
use ClgsRu\LaravelRabbitMQ\Contracts\BaseConfig;

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
<?php

namespace Arhitov\LaravelRabbitMQ\Contracts;

use Exception;

abstract class BaseConfig implements InterfaceConfig
{
    const LIST_ALLOWED_PROPERTY_FOR_GET = [];

    /**
     * @throws Exception
     */
    public function __get($name)
    {
        if (in_array($name, static::LIST_ALLOWED_PROPERTY_FOR_GET)) {
            return $this->$name;
        }
        throw new Exception ("Property $name is not defined");
    }
}
<?php

namespace Arhitov\LaravelRabbitMQ\Connections;

use Arhitov\LaravelRabbitMQ\Contracts\BaseConfig;
use Arhitov\LaravelRabbitMQ\Contracts\ConnectionConfig;

/**
 * @property string $host
 * @property string $port
 * @property string $user
 * @property string $password
 * @property string $vhost
 */
class Config extends BaseConfig implements ConnectionConfig
{
    const LIST_ALLOWED_PROPERTY_FOR_GET = ['host', 'port', 'user', 'password', 'vhost'];

    protected string $host;
    protected string $port;
    protected string $user;
    protected string $password;
    protected string $vhost;

    public function __construct()
    {
        $config = config('rabbitmq');
        $host = $config['hosts'][0];
        foreach (['host', 'port', 'user', 'password', 'vhost'] as $key) {
            if (array_key_exists($key, $host)) {
                $this->$key = $host[$key];
            }
        }
    }

    public function getAttributesForStreamConnection(): array
    {
        return [
            $this->host,
            $this->port,
            $this->user,
            $this->password,
            $this->vhost,
        ];
    }
}
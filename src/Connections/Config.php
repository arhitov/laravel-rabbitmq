<?php

namespace Arhitov\LaravelRabbitMQ\Connections;

use Arhitov\LaravelRabbitMQ\Contracts\ConnectionConfig;

class Config implements ConnectionConfig
{
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

    /**
     * @return array
     */
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

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getPort(): string
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getVhost(): string
    {
        return $this->vhost;
    }
}
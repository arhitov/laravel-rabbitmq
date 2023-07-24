<?php

namespace Arhitov\LaravelRabbitMQ\Contracts;

interface ConnectionConfig extends InterfaceConfig
{
    public function __construct();

    public function getAttributesForStreamConnection(): array;

    /**
     * @return string
     */
    public function getHost(): string;

    /**
     * @return string
     */
    public function getPort(): string;

    /**
     * @return string
     */
    public function getUser(): string;

    /**
     * @return string
     */
    public function getPassword(): string;

    /**
     * @return string
     */
    public function getVhost(): string;
}
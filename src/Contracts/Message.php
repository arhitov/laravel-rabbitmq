<?php

namespace ClgsRu\LaravelRabbitMQ\Contracts;

interface Message
{
    public function getExchange(): ?string;
    public function getQueue(): ?string;
    public function getRoutingKey(): ?string;
    public function getBody();
    public function setBody($body): void;
    public function getTimestamp(): ?int;
    public function hasBodySerialize(): bool;
    public function getBodySerialize(): string;
    public function setBodySerialize(string $body): void;
    public function hasHeaders(): bool;
    public function getHeaders(): array;
    public function setDeliveryMode(int $mode): void;
}
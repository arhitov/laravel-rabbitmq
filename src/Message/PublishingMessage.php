<?php

namespace Arhitov\LaravelRabbitMQ\Message;

use Arhitov\LaravelRabbitMQ\Contracts\MessageSerializer;
use Arhitov\LaravelRabbitMQ\Contracts\Publisher as ContractsPublisher;
use Arhitov\LaravelRabbitMQ\Contracts\PublisherMessage;
use Arhitov\LaravelRabbitMQ\Exception\PublisherException;
use Arhitov\LaravelRabbitMQ\Exception\MessageException;
use Arhitov\LaravelRabbitMQ\Exception\MessageSerializerException;
use Illuminate\Support\Facades\App;

class PublishingMessage extends AbstractMessage implements PublisherMessage
{
    protected ?ContractsPublisher $publisher = null;

    /**
     * @param string $body
     * @throws MessageException
     */
    public function setBody($body): void
    {
        parent::setBody($body);
        try {
            (App::make(MessageSerializer::class))->serialize($this);
        } catch (MessageSerializerException $e) {
            throw new MessageException('MessageSerializerException : ' . $e->getMessage());
        }
    }

    /**
     * @param array $headers
     * @return void
     */
    public function setHeaders(array $headers): void
    {
        $this->headers = array_merge($this->headers, $headers);
    }

    /**
     * @param ContractsPublisher $publisher
     * @return void
     */
    public function bindPublisher(ContractsPublisher $publisher): void
    {
        $this->publisher = $publisher;
    }

    /**
     * @throws PublisherException
     */
    public function push(): void
    {
        if (is_null($this->publisher)) {
            throw new PublisherException('Publisher not bind');
        }

        $this->publisher->push($this);
    }
}
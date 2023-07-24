<?php

namespace Arhitov\LaravelRabbitMQ;

use Illuminate\Support\ServiceProvider;
use Arhitov\LaravelRabbitMQ\Contracts\Connection as ContractsConnection;
use Arhitov\LaravelRabbitMQ\Connections\ConnectionFactory;
use Arhitov\LaravelRabbitMQ\Contracts\MessageSerializer;
use Arhitov\LaravelRabbitMQ\Message\Serializers\JsonSerializer;
use Arhitov\LaravelRabbitMQ\Contracts\MessageDeserializer;
use Arhitov\LaravelRabbitMQ\Message\Deserializers\JsonDeserializer;
use Arhitov\LaravelRabbitMQ\Contracts\Consumer as ContractsConsumer;
use Arhitov\LaravelRabbitMQ\Consumer\Consumer;
use Arhitov\LaravelRabbitMQ\Contracts\Publisher as ContractsPublisher;
use Arhitov\LaravelRabbitMQ\Publisher\Publisher;
use Arhitov\LaravelRabbitMQ\Console\Commands\ConsumerCommand;
use Arhitov\LaravelRabbitMQ\Console\Commands\PublisherCommand;
use Arhitov\LaravelRabbitMQ\Console\Commands\QueuePurgeCommand;

class LaravelRabbitMQServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/rabbitmq.php',
            'rabbitmq'
        );

        $this->app->singleton(ContractsConnection::class, fn() => ConnectionFactory::build());

        $this->app->bind(MessageSerializer::class, JsonSerializer::class);
        $this->app->bind(MessageDeserializer::class, JsonDeserializer::class);

        $this->app->bind(ContractsConsumer::class, Consumer::class);
        $this->app->bind(ContractsPublisher::class, Publisher::class);
    }

    /**
     * Register the application's event listeners.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/rabbitmq.php' => config_path('rabbitmq.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ConsumerCommand::class,
                PublisherCommand::class,
                QueuePurgeCommand::class,
            ]);
        }
    }
}

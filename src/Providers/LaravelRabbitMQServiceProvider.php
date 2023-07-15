<?php

namespace ClgsRu\LaravelRabbitMQ\Providers;

use Illuminate\Support\ServiceProvider;
use ClgsRu\LaravelRabbitMQ\Contracts\Connection as ContractsConnection;
use ClgsRu\LaravelRabbitMQ\Connections\ConnectionFactory;
use ClgsRu\LaravelRabbitMQ\Contracts\MessageSerializer;
use ClgsRu\LaravelRabbitMQ\Message\Serializers\JsonSerializer;
use ClgsRu\LaravelRabbitMQ\Contracts\MessageDeserializer;
use ClgsRu\LaravelRabbitMQ\Message\Deserializers\JsonDeserializer;
use ClgsRu\LaravelRabbitMQ\Contracts\Consumer as ContractsConsumer;
use ClgsRu\LaravelRabbitMQ\Consumers\Consumer as Consumer;
use ClgsRu\LaravelRabbitMQ\Contracts\Publisher as ContractsPublisher;
use ClgsRu\LaravelRabbitMQ\Publishers\Publisher;
use ClgsRu\LaravelRabbitMQ\Console\Commands\ConsumerCommand;
use ClgsRu\LaravelRabbitMQ\Console\Commands\PublisherCommand;

class LaravelRabbitMQServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/rabbitmq.php',
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
            __DIR__ . '/../../config/rabbitmq.php' => config_path('rabbitmq.php'),
        ], 'laravel-rabbitmq-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ConsumerCommand::class,
                PublisherCommand::class,
            ]);
        }
    }
}

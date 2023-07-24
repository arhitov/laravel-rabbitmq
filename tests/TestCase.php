<?php

namespace Arhitov\LaravelRabbitMQ\Tests;

use Arhitov\LaravelRabbitMQ\Queue\Config as QueueConfig;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Arhitov\LaravelRabbitMQ\Contracts\Connection as ContractsConnection;
use Arhitov\LaravelRabbitMQ\Contracts\Publisher as ContractsPublisher;
use Arhitov\LaravelRabbitMQ\Contracts\Consumer as ContractsConsumer;
use Arhitov\LaravelRabbitMQ\LaravelRabbitMQServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use PhpAmqpLib\Exception\AMQPExceptionInterface;

abstract class TestCase extends BaseTestCase
{
    const QUEUE_NAME = 'queue_for_tests_packages_arhitov_laravelrabbitmq';
    const QUEUE_NAME_COUNT = 3;
    protected ContractsConnection $connection;
    protected ContractsPublisher $publisher;
    protected ContractsConsumer $consumer;

    protected function getPackageProviders($app): array
    {
        return [
            LaravelRabbitMQServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = App::make(ContractsConnection::class);
        $this->publisher = App::make(ContractsPublisher::class);
        $this->consumer = App::make(ContractsConsumer::class);
    }
    protected function getEnvironmentSetUp($app)
    {
        // make sure, our .env file is loaded
        $app->useEnvironmentPath(__DIR__.'/..');
        $app->bootstrapWith([LoadEnvironmentVariables::class]);
        parent::getEnvironmentSetUp($app);
        $app['config']->set('rabbitmq', include __DIR__ . '/../config/rabbitmq.php');
    }

    protected function connectTestOrSkipped(): void
    {
        try {
            $this->consumer->connect();
        } catch (AMQPExceptionInterface $e) {
            $this->markTestSkipped('Test skipped because connection not established');
        }
        if (! $this->consumer->isConnected()) {
            $this->markTestSkipped('Test skipped because connection not established');
        }
    }

    protected function clearQueue($queue_setting = null): void
    {
        $queue_setting ??= self::QUEUE_NAME;
        $queue = $this->consumer->bindQueue($queue_setting);
        $queue->delete();
    }
}
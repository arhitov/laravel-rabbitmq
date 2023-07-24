<?php

namespace Arhitov\LaravelRabbitMQ\Console\Commands;

use Arhitov\LaravelRabbitMQ\Contracts\Publisher as ContractsPublisher;
use Arhitov\LaravelRabbitMQ\Exception\PublisherException as PublisherException;
use Arhitov\LaravelRabbitMQ\Message\PublishingMessage;
use Illuminate\Console\Command;

class PublisherCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:publisher {message : Message to be sent}
                        {--E|exchange= : The name of the exchanger to which the message will be sent}
                        {--Q|queue= : The name of the queue where the message will be sent. (If the exchanger is not specified)}
                        {--R|routing_key= : Routing key}';
    protected $description = 'A RabbitMQ Publisher.';

    /**
     * @param ContractsPublisher $publisher
     * @return void
     */
    public function handle(ContractsPublisher $publisher): void
    {
        if (! ($exchange = $this->option('exchange'))) {
            if (! ($queue = $this->option('queue'))) {
                $this->error('Please indicate exchange or queue');
                return;
            }
        }

        $message = new PublishingMessage(
            $exchange ?? '',
            $queue ?? '',
            $this->argument('message'),
            $this->option('routing_key')
        );

        try {
            $publisher->push($message);
        } catch (PublisherException $e) {
            $this->error('PublisherException: ' . $e->getMessage());
        }
    }
}
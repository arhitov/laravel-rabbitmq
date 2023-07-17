<?php

namespace ClgsRu\LaravelRabbitMQ\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use ClgsRu\LaravelRabbitMQ\Contracts\Consumer as ContractsConsumer;
use ClgsRu\LaravelRabbitMQ\Queue\Config as QueueConfig;
use Exception;

class QueuePurgeCommand extends Command
{
    use ConfirmableTrait;

    protected $signature = 'rabbitmq:queue-purge {queue}
                           {--force : Force the operation to run when in production}';

    protected $description = 'Purge all messages in queue';

    /**
     * @param ContractsConsumer $consumer
     */
    public function handle(ContractsConsumer $consumer): void
    {
        if (! $this->confirmToProceed()) {
            return;
        }

        $queue_name = $this->argument('queue');
        $queue_config = new QueueConfig($queue_name);
        $queue = $consumer->makeQueue($queue_config);
        $queue->purge();

        $this->info('Queue purged successfully');
    }
}

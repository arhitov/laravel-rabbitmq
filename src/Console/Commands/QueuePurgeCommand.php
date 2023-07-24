<?php

namespace Arhitov\LaravelRabbitMQ\Console\Commands;

use Arhitov\LaravelRabbitMQ\Exception\QueueException;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Arhitov\LaravelRabbitMQ\Contracts\Consumer as ContractsConsumer;

class QueuePurgeCommand extends Command
{
    use ConfirmableTrait;

    protected $signature = 'rabbitmq:queue-purge {queue}
                           {--force : Force the operation to run when in production}';

    protected $description = 'Purge all messages in queue';

    /**
     * @param ContractsConsumer $consumer
     * @return void
     * @throws QueueException
     */
    public function handle(ContractsConsumer $consumer): void
    {
        if (! $this->confirmToProceed()) {
            return;
        }

        $queue_name = $this->argument('queue');
        $queue = $consumer->bindQueue($queue_name);
        $queue->purge();

        $this->info('Queue purged successfully');
    }
}

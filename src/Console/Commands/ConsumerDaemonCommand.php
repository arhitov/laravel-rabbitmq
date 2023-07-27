<?php

namespace Arhitov\LaravelRabbitMQ\Console\Commands;

use Arhitov\LaravelRabbitMQ\Exception\ConfigException;
use Arhitov\LaravelRabbitMQ\Message\ConsumedMessage;
use Arhitov\LaravelRabbitMQ\Contracts\Consumer as ContractsConsumer;
use Arhitov\LaravelRabbitMQ\Exception\QueueException;
use Arhitov\LaravelRabbitMQ\Exception\MessageException;
use Arhitov\LaravelRabbitMQ\Exception\FaitSetPropertyException;
use Illuminate\Console\Command;

class ConsumerDaemonCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:consumer_daemon {queue : Name listening queue}';
    protected $description = 'A RabbitMQ Consumer daemon.';

    /**
     * @param ContractsConsumer $consumer
     * @return void
     * @throws QueueException
     * @throws FaitSetPropertyException
     * @throws ConfigException
     */
    public function handle(ContractsConsumer $consumer): void
    {
        $queue_name = $this->argument('queue');
        $memory_begin = memory_get_usage(true);
        $queue = $consumer->bindQueue($queue_name);
        $daemon = $queue->getDaemon();
        $daemon->setOptions('timeout_for_wait', 10000);
        $daemon->setOptions('timeout_wait_message', 10);

        $daemon->subscribe(function (ConsumedMessage $message) use ($daemon) {
            $this->info('Message: ' . implode(', ', (function() use ($message) {
                    $result = [];
                    foreach (['Exchange', 'Queue', 'RoutingKey', 'Timestamp'] as $name) {
                        $method = 'get' . $name;
                        $value = $message->{$method}();
                        if (! empty($value)) {
                            $result[] = $name . '=' . $value;
                        }
                    }
                    return $result;
                })()));
            try {
                $body = $message->getBody();
            } catch (MessageException $e) {
                $this->error($e->getMessage());
                $body = 'ERROR';
            }
            dump($body);
            $this->info('JobsProcessed: ' . $daemon->getJobsProcessed());
            if (is_string($body) && in_array($body, ['ERROR', 'STOP'])) {
                $daemon->stop();
            }
        });

        $daemon->start();

        $daemon->getError()
            ? $this->error('ERROR: ' . $daemon->getError()->getMessage())
            : $this->info('Stopped');

        $this->table(
            ['Point', 'Memory use'],
            [
                ['point' => 'begin', 'memory_use' => $memory_begin],
                ['point' => 'end', 'memory_use' => memory_get_usage(true)],
            ]
        );
    }
}
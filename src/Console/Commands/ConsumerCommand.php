<?php

namespace ClgsRu\LaravelRabbitMQ\Console\Commands;

use Illuminate\Console\Command;
use ClgsRu\LaravelRabbitMQ\Contracts\Consumer as ContractsConsumer;
use ClgsRu\LaravelRabbitMQ\Queue\Config as QueueConfig;
use ClgsRu\LaravelRabbitMQ\Exception\QueueException;
use ClgsRu\LaravelRabbitMQ\Exception\MessageException;
use Exception;

class ConsumerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:consumer {queue : Name listening queue}
                        {--D|delay=5 : Delay time after reading each message}';
    protected $description = 'A RabbitMQ Consumer.';

    /**
     * @throws Exception
     */
    public function handle(ContractsConsumer $consumer): void
    {
        $queue_name = $this->argument('queue');
        $queue_config = new QueueConfig($queue_name);
        $queue = $consumer->makeQueue($queue_config);

        while (true) {
            try {
                $message = $queue->pop();
                if (! is_null($message)) {
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
                    dump($message->getBody());
                }
            } catch (QueueException|MessageException $e) {
                $this->error(get_class($e) . ': ' . $e->getMessage());
            }

            sleep((int)($this->option('delay') ?? 5));
            usleep(200000);// 0.2s обязательная задержка, чтобы цикл не утилизировал CPU под 100%
        }
    }
}
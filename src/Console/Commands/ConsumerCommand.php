<?php

namespace Arhitov\LaravelRabbitMQ\Console\Commands;

use Illuminate\Console\Command;
use Arhitov\LaravelRabbitMQ\Contracts\Consumer as ContractsConsumer;
use Arhitov\LaravelRabbitMQ\Queue\Config as QueueConfig;
use Arhitov\LaravelRabbitMQ\Exception\QueueException;
use Arhitov\LaravelRabbitMQ\Exception\MessageException;
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

        $memory_last = memory_get_usage(true);
        $this->error('Memory: ' . $memory_last);

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
                $memory_now = memory_get_usage(true);
                $memory_last_10 = $memory_last / 10;
                if ($memory_now > $memory_last + $memory_last_10 || $memory_now < $memory_last - $memory_last_10) {
                    $memory_last = $memory_now;
                    $this->error('Memory: ' . $memory_last);
                }
            } catch (QueueException|MessageException $e) {
                $this->error(get_class($e) . ': ' . $e->getMessage());
            }

            sleep((int)($this->option('delay') ?? 5));
            usleep(200000);// 0.2s обязательная задержка, чтобы цикл не утилизировал CPU под 100%
        }
    }
}
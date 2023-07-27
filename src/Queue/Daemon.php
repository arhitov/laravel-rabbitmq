<?php

namespace Arhitov\LaravelRabbitMQ\Queue;

use Arhitov\LaravelRabbitMQ\Contracts\Connection;
use Arhitov\LaravelRabbitMQ\Exception\FaitSetPropertyException;
use Arhitov\LaravelRabbitMQ\Exception\QueueDaemonTimeOutWaitMessageException;
use Arhitov\LaravelRabbitMQ\Exception\QueueException;
use Exception;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

class Daemon
{
    const STATUS_CREATED = 1;
    const STATUS_PREPARED = 2;
    const STATUS_LAUNCH = 3;
    const STATUS_WORKING = 4;
    const STATUS_STOPPING = -1;
    const STATUS_STOPPED = 0;
    const STATUS_TIMEOUT_WAIT_MESSAGE = -50;
    const STATUS_CRASHED = -99;

    protected Connection $connect;
    protected Queue $queue;
    protected Config $queue_config;
    protected array $consumer_tags = [];
    protected int $status;
    protected array $options = [
        'timeout_for_wait' => 10000, // (microseconds)
        'timeout_sleep_while' => 10000, // (microseconds)
        'timeout_wait_message' => 10, // (seconds) Time to wait for a message before closing the connection
    ];

    /**
     * Confirmation of the execution of a message from the queue
     * @var bool
     */
    protected bool $message_confirm = true;

    protected int $jobsProcessed = 0;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var Exception|Throwable|null
     */
    protected $error = null;
    protected int $timer_wait_message = 0;

    /**
     * @param Connection $connect
     * @param Queue $queue
     * @param Config $queue_config
     * @param bool|null $message_confirm Confirmation of the execution of a message from the queue
     */
    public function __construct(Connection $connect, Queue $queue, Config $queue_config, ?bool $message_confirm = null)
    {
        $this->connect = $connect;
        $this->queue = $queue;
        $this->queue_config = $queue_config;
        $this->message_confirm = $message_confirm ?? $this->message_confirm;
        $this->status = self::STATUS_CREATED;
    }

    /**
     * @param array|string $name
     * @param mixed $value
     * @return void
     * @throws QueueException
     */
    public function setOptions($name, $value): void
    {
        if (is_string($name)) {
            $name = [$name => $value];
        } elseif (! is_array($name)) {
            throw new QueueException('Parameter structure error');
        }
        foreach ($name as $key => $value) {
            if (! array_key_exists($key, $this->options)) {
                throw new QueueException('Option "' . $key . '" not supported');
            }
            if (gettype($value) !== gettype($this->options[$key])) {
                throw new QueueException('The "' . $key . '" parameter contains the wrong data type');
            }
            $this->options[$key] = $value;
        }
    }

    /**
     * @return int
     */
    public function getJobsProcessed(): int
    {
        return $this->jobsProcessed;
    }

    /**
     * @return Exception|Throwable|null
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param callable $callback
     * @return void
     */
    public function subscribe(callable $callback): void
    {
        $this->callback = $callback;
        $this->status = self::STATUS_PREPARED;
    }

    /**
     * @return void
     * @throws QueueException
     */
    public function start(): void
    {
        if (! $this->isPrepared()) {
            throw new QueueException('Daemon is not prepared for started');
        }
        $this->status = self::STATUS_LAUNCH;
        $this->error = null;

        $arguments = []; // pass

        $prefetchSize = null;    // message size in bytes or null, otherwise error
        $prefetchCount = 1;      // prefetch count value
        $applyPerChannel = null; // can be false or null, otherwise error
        $this->connect->channel()->basic_qos($prefetchSize, $prefetchCount, $applyPerChannel);

        $this->startTimerWaitMessage();

        foreach ($this->queue_config->getNameList() as $queue_name) {
            $consumer_tag = $this->queue->getConsumerTag($queue_name);
            $this->consumer_tags[] = $this->connect->channel()->basic_consume(
                $queue_name,
                $consumer_tag,
                false,
                ! $this->message_confirm,
                false,
                false,
                function (AMQPMessage $AMQPMessage) use ($queue_name): void {
                    $this->startTimerWaitMessage();
                    $this->runJob($queue_name, $AMQPMessage);
                },
                null,
                $arguments
            );
        }

        try {
            while ($this->connect->channel()->is_consuming()) {
                $this->status = self::STATUS_WORKING;

                if (!$this->isAllowWork()) {
                    break;
                }

                if (! $this->checkTimerWaitMessage()) {
                    throw new QueueDaemonTimeOutWaitMessageException('Time out for wait message');
                }

                $this->connect->channel()->wait(null, true, $this->options['timeout_for_wait']);

                usleep($this->options['timeout_sleep_while']);
            }
            $this->status = self::STATUS_STOPPED;
        } catch (QueueDaemonTimeOutWaitMessageException $e) {
            $this->status = self::STATUS_TIMEOUT_WAIT_MESSAGE;
            $this->error = $e;
        } catch (Exception|Throwable $e) {
            $this->status = self::STATUS_CRASHED;
            $this->error = $e;
        }
    }

    /**
     * @return void
     */
    public function stop(): void
    {
        $this->status = self::STATUS_STOPPING;
        foreach ($this->consumer_tags as $consumer_tag) {
            $this->connect->channel()->basic_cancel($consumer_tag, false, true);
        }
        $this->connect->channel()->stopConsume();
    }

    /**
     * @return bool
     */
    public function isRunning(): bool
    {
        return $this->status === self::STATUS_WORKING;
    }

    /**
     * @return bool
     */
    public function isPrepared(): bool
    {
        return in_array($this->status, [self::STATUS_PREPARED, self::STATUS_PREPARED, self::STATUS_STOPPED, self::STATUS_CRASHED]);
    }

    /**
     * @return bool
     */
    public function isStopped(): bool
    {
        return in_array($this->status, [self::STATUS_CREATED, self::STATUS_PREPARED, self::STATUS_STOPPED, self::STATUS_CRASHED]);
    }

    /**
     * @return bool
     */
    protected function isAllowWork(): bool
    {
        return in_array($this->status, [self::STATUS_LAUNCH, self::STATUS_WORKING]);
    }

    /**
     * @param string $queue_name
     * @param AMQPMessage $AMQPMessage
     * @return void
     * @throws FaitSetPropertyException
     */
    protected function runJob(string $queue_name, AMQPMessage $AMQPMessage): void
    {
        $this->jobsProcessed += 1;
        $consumedMessage = $this->queue->makeConsumedMessage($queue_name, $AMQPMessage);
        call_user_func($this->callback, $consumedMessage);
        if ($this->message_confirm) {
            $AMQPMessage->ack();
        }
    }

    /**
     * @return void
     */
    protected function startTimerWaitMessage(): void
    {
        $this->timer_wait_message = $this->options['timeout_wait_message'] + time();
    }

    /**
     * @return bool
     */
    protected function checkTimerWaitMessage(): bool
    {
        return $this->timer_wait_message > time();
    }
}
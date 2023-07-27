<?php

namespace Arhitov\LaravelRabbitMQ\Tests\Feature;

use Arhitov\LaravelRabbitMQ\Exception\ConfigException;
use Arhitov\LaravelRabbitMQ\Exception\PublisherException;
use Arhitov\LaravelRabbitMQ\Exception\QueueException;
use Arhitov\LaravelRabbitMQ\Message\ConsumedMessage;
use Arhitov\LaravelRabbitMQ\Tests\TestCase;

class QueueDaemonTest extends TestCase
{
    /**
     * @throws QueueException
     * @throws ConfigException
     * @throws PublisherException
     */
    public function testSubscribe(): void
    {
        $limit_message = 2;
        $this->connectTestOrSkipped();
        $this->clearQueue();

        // Create queue
        $queue = $this->consumer->bindQueue(self::QUEUE_NAME);
        $this->assertTrue($queue->declare(), 'Fail declares queue');
        $info = $queue->getInfoList();
        $this->assertArrayHasKey(self::QUEUE_NAME, $info, 'Queue name not in list information');
        $this->assertTrue($info[self::QUEUE_NAME]['success'], 'Queue not exists');

        // Main body test
        $daemon = $queue->getDaemon(true);
        $daemon->setOptions('timeout_wait_message', 2);
        $limit_message_counter = $limit_message;

        $daemon->subscribe(function (ConsumedMessage $message) use ($daemon, &$limit_message_counter) {
            $limit_message_counter -= 1;
            if ($limit_message_counter == 0) {
                $daemon->stop();
            }
        });

        $queue_payload = 'payload_' . rand(1000, 9999);
        for ($i = $limit_message; $i > 0; $i -= 1) {
            $this->publisher->createMessage(
                null,
                self::QUEUE_NAME,
                $queue_payload
            )->push();
        }

        $daemon->start();

        $this->assertNull($daemon->getError(), 'Processing failed');
        $this->assertEquals($limit_message, $daemon->getJobsProcessed(), 'The number of messages processed is not equal to the number of messages sent');

        // Delete queue
        $this->assertTrue($queue->delete(), 'Fail delete queue');
        $info = $queue->getInfoList();
        $this->assertArrayHasKey(self::QUEUE_NAME, $info, 'Queue name not in list information');
        $this->assertFalse($info[self::QUEUE_NAME]['success'], 'Queue exists');
        $this->assertEquals(404, $info[self::QUEUE_NAME]['code'], 'Queue code not 404');
    }
}
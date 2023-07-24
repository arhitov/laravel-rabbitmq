<?php

namespace Arhitov\LaravelRabbitMQ\Tests\Feature;

use Arhitov\LaravelRabbitMQ\Exception\ConfigException;
use Arhitov\LaravelRabbitMQ\Exception\FaitSetPropertyException;
use Arhitov\LaravelRabbitMQ\Exception\PublisherException;
use Arhitov\LaravelRabbitMQ\Exception\QueueException;
use Arhitov\LaravelRabbitMQ\Tests\TestCase;
use Arhitov\LaravelRabbitMQ\Queue\Config as QueueConfig;

class QueueMultiModeTest extends TestCase
{
    /**
     * @throws QueueException
     * @throws ConfigException
     */
    public function testCreateAndDeleteV1(): void
    {
        $this->createMultiMode([self::QUEUE_NAME . '_%N%', self::QUEUE_NAME_COUNT]);
    }

    /**
     * @throws ConfigException
     * @throws QueueException
     */
    public function testCreateAndDeleteV2(): void
    {
        $this->createMultiMode(['name' => [self::QUEUE_NAME . '_%N%', self::QUEUE_NAME_COUNT]]);
    }

    /**
     * @throws QueueException
     * @throws ConfigException
     * @throws PublisherException
     * @throws FaitSetPropertyException
     */
    public function testPushMessageAndPop(): void
    {
        $this->connectTestOrSkipped();
        $this->clearQueue([self::QUEUE_NAME . '_%N%', self::QUEUE_NAME_COUNT]);
        $queue = $this->consumer->bindQueue(
            new QueueConfig([self::QUEUE_NAME . '_%N%', self::QUEUE_NAME_COUNT])
        );
        $this->assertTrue($queue->declare(), 'Fail declares queue');

        $queue_name = self::QUEUE_NAME . '_1';
        $queue_payload = 'payload_' . rand(1000, 9999);

        $this->publisher->createMessage(
            null,
            $queue_name,
            $queue_payload
        )->push();

        $queue_one = $this->consumer->bindQueue(new QueueConfig(self::QUEUE_NAME . '_1'));
        $message = $queue_one->pop();
        $this->assertNotNull($message, 'Message empty');
        $this->assertEquals($queue_payload, $message->getBody(), 'The body of the message is not equal to the test message');
        $this->assertTrue($queue->delete(), 'Fail delete queue');
    }

    /**
     * @throws ConfigException
     * @throws QueueException
     */
    protected function createMultiMode(array $config): void
    {
        $this->connectTestOrSkipped();
        $this->clearQueue($config);
        $queue_config = new QueueConfig($config);
        $queue = $this->consumer->bindQueue($queue_config);
        $info = $queue->getInfo();
        foreach ($info as $queue_name => $queue_data) {
            $this->assertFalse($queue_data['success'], 'Queue "' . $queue_name . '" exists');
            $this->assertEquals(404, $queue_data['code'], 'Queue code not 404');
        }
        $this->assertTrue($queue->declare(), 'Fail declares queue');
        $info = $queue->getInfo();
        foreach ($info as $queue_name => $queue_data) {
            $this->assertTrue($queue_data['success'], 'Queue "' . $queue_name . '" not exists');
        }
        $this->assertTrue($queue->delete(), 'Fail delete queue');
    }
}
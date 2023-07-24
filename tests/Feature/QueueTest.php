<?php

namespace Arhitov\LaravelRabbitMQ\Tests\Feature;

use Arhitov\LaravelRabbitMQ\Exception\FaitSetPropertyException;
use Arhitov\LaravelRabbitMQ\Exception\PublisherException;
use Arhitov\LaravelRabbitMQ\Exception\QueueException;
use Arhitov\LaravelRabbitMQ\Tests\TestCase;

class QueueTest extends TestCase
{
    /**
     * @throws QueueException
     */
    public function testCreateAndDelete(): void
    {
        $this->connectTestOrSkipped();
        $this->clearQueue();
        $queue = $this->consumer->bindQueue(self::QUEUE_NAME);
        $this->assertTrue($queue->declare(), 'Fail declares queue');
        $info = $queue->getInfo();
        $this->assertArrayHasKey(self::QUEUE_NAME, $info, 'Queue name not in list information');
        $this->assertTrue($info[self::QUEUE_NAME]['success'], 'Queue not exists');
        $this->assertTrue($queue->delete(), 'Fail delete queue');
        $info = $queue->getInfo();
        $this->assertArrayHasKey(self::QUEUE_NAME, $info, 'Queue name not in list information');
        $this->assertFalse($info[self::QUEUE_NAME]['success'], 'Queue exists');
        $this->assertEquals(404, $info[self::QUEUE_NAME]['code'], 'Queue code not 404');
    }

    /**
     * @throws PublisherException
     * @throws QueueException
     * @throws FaitSetPropertyException
     */
    public function testPushMessageAndPop(): void
    {
        $this->connectTestOrSkipped();
        $this->clearQueue();
        $queue_payload = 'payload_' . rand(1000, 9999);
        $queue = $this->consumer->bindQueue(self::QUEUE_NAME);
        $queue->declare();
        $this->publisher->createMessage(
            null,
            self::QUEUE_NAME,
            $queue_payload
        )->push();
        $message = $queue->pop();
        $this->assertNotNull($message, 'Message empty');
        $this->assertEquals($queue_payload, $message->getBody(), 'The body of the message is not equal to the test message');
        $message = $queue->pop();
        $this->assertNull($message, 'Queue not empty');
        $this->assertTrue($queue->delete(), 'Fail delete queue');
    }

    /**
     * @throws QueueException
     */
    public function testGetInfo(): void
    {
        $this->connectTestOrSkipped();
        $this->clearQueue();
        $queue = $this->consumer->bindQueue(self::QUEUE_NAME);
        $info = $queue->getInfo();
        $this->assertArrayHasKey(self::QUEUE_NAME, $info, 'Queue name in list information');
        foreach (['success', 'code', 'error'] as $key) {
            $this->assertArrayHasKey($key, $info[self::QUEUE_NAME], 'Checking for the presence of the "' . $key . '" key in the response structure');
        }
        $this->assertFalse($info[self::QUEUE_NAME]['success'], 'Queue not exists');
        $this->assertIsInt($info[self::QUEUE_NAME]['code'], 'Key "code" is integer');
        $this->assertIsString($info[self::QUEUE_NAME]['error'], 'Key "error" is string');
        $this->assertTrue($queue->declare(), 'Declares queue, creates if needed');
        $info = $queue->getInfo();
        $this->assertArrayHasKey(self::QUEUE_NAME, $info, 'Queue name in list information');
        foreach (['success', 'message_count', 'consumer_count'] as $key) {
            $this->assertArrayHasKey($key, $info[self::QUEUE_NAME], 'Checking for the presence of the "' . $key . '" key in the response structure');
        }
        $this->assertTrue($info[self::QUEUE_NAME]['success'], 'Queue exists');
        $this->assertIsInt($info[self::QUEUE_NAME]['message_count'], 'Key "message_count" is integer');
        $this->assertIsInt($info[self::QUEUE_NAME]['consumer_count'], 'Key "consumer_count" is integer');
        $this->assertTrue($queue->delete(), 'Delete queue');
    }
}
<?php

namespace Arhitov\LaravelRabbitMQ\Tests\Feature;

use Arhitov\LaravelRabbitMQ\Exception\ConfigException;
use Arhitov\LaravelRabbitMQ\Exception\ExchangeException;
use Arhitov\LaravelRabbitMQ\Mapping\Mapper;
use Arhitov\LaravelRabbitMQ\Tests\TestCase;

class MapperTest extends TestCase
{
    /**
     * @throws ConfigException
     * @throws ExchangeException
     */
    public function testExecute(): void
    {
        $this->connectTestOrSkipped();
        $config = include __DIR__ . '/../../config/example_array_mapping.php';
        $mapper = new Mapper($this->connection);
        $this->clear($mapper);

        foreach ($mapper->getQueueList() as $queue) {
            $info = $queue->getInfo();
            foreach ($info as $queue_name => $queue_data) {
                $this->assertFalse($queue_data['success'], 'Queue "' . $queue_name . '" exists');
                $this->assertEquals(404, $queue_data['code'], 'Queue code not 404');
            }
        }

        $this->assertInstanceOf(Mapper::class, $mapper, 'Mapper not instance of Mapper');
        $mapper->loadConfig($config);
        $mapper->execute(true);

        foreach ($mapper->getQueueList() as $queue) {
            $info = $queue->getInfo();
            foreach ($info as $queue_name => $queue_data) {
                $this->assertTrue($queue_data['success'], 'Queue "' . $queue_name . '" not exists');
            }
        }

        $this->clear($mapper);
    }

    /**
     * Clear
     * @param Mapper $mapper
     * @return void
     */
    protected function clear(Mapper $mapper): void
    {
        foreach ($mapper->getExchangeList() as $exchange) {
            $exchange->delete();
        }

        foreach ($mapper->getQueueList() as $queue) {
            $queue->delete();
        }
    }
}
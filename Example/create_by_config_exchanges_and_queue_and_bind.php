<?php

use Arhitov\LaravelRabbitMQ\Exception\Exception;
use Arhitov\LaravelRabbitMQ\Mapping\Mapper;
use Illuminate\Support\Facades\App;

$config = include __DIR__ . '/../config/example_array_mapping.php';
/**
 * @var Mapper $mapper
 */
$mapper = App::make(Mapper::class);

try {
    $mapper->loadConfig($config);
    $mapper->execute(true);
} catch (Exception $e) {
}

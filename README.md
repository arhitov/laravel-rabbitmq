Package RabbitMQ for Laravel
==============

![PHPUnit tests](Image/badge.svg)

The LaravelRabbitMQ component is a de facto package that was designed to help you interact with the RabbitMQ message broker. LaravelRabbitMQ is based on php-amqplib.

## Supported
* [x] Simple Queue
* [x] Exchange
* [ ] Queue Job

## Requirements
* PHP (7.4+)
* php-amqplib/php-amqplib (3.0+)
* laravel/framework (8.0+)

## Setup
Ensure you have [composer](http://getcomposer.org) installed, then run the following command:

```bash
$ composer require arhitov/laravel-rabbitmq
```

And save configuration file

```bash
$ php artisan vendor:publish --tag="config"
```

## Usage

See [Example](Example) folder. Also, you can ask me.

## Tests
To successfully run the tests you need to first have a stock RabbitMQ broker running locally.Then, run tests like this:

```bash
$ php ./vendor/bin/testbench package:test
```

## Sponsor my work!
If you think this package helped you in any way, you can sponsor me! I am a free developer, so your help will be very helpful to me. :blush:

## License

LaravelRabbitMQ is open-sourced software licensed under the [MIT license](LICENSE.md).
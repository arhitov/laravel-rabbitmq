Package RabbitMQ for Laravel
==============

![PHPUnit tests][ico-phpunit-tests]
![PHP][ico-php-support]
![amqplib][ico-amqplib-support]
[![Laravel][ico-laravel-support]][link-laravel-support]
[![Software License][ico-license]][link-license]

The LaravelRabbitMQ component is a de facto package that was designed to help you interact with the RabbitMQ message broker. LaravelRabbitMQ is based on php-amqplib.

## Supported
* [x] Simple Queue
* [x] Exchange
* [x] Daemon
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

See [Example][link-example] folder. Also, you can ask me.

## Tests
To successfully run the tests you need to first have a stock RabbitMQ broker running locally.Then, run tests like this:

```bash
$ php ./vendor/bin/testbench package:test
```

## Sponsor my work!
If you think this package helped you in any way, you can sponsor me! I am a free developer, so your help will be very helpful to me. :blush:

## License

LaravelRabbitMQ is open-sourced software licensed under the [MIT license][link-license].

## Authors
Alexander Arhitov [clgsru@gmail.com](mailto:clgsru@gmail.com)

Welcome here! :metal: 

[ico-phpunit-tests]: Image/badge.svg
[ico-php-support]: https://img.shields.io/badge/PHP-7.4+-blue.svg
[ico-amqplib-support]: https://img.shields.io/badge/amqplib-3.0+-blue.svg
[ico-laravel-support]: https://img.shields.io/badge/Laravel-8.x-blue.svg
[link-laravel-support]: https://laravel.com/docs/8.x/
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg
[link-license]: LICENSE.md
[link-example]: Example
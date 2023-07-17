<?php

return [
    'exchange' => [
        'exchange_a' => [
            // The default values will be used here.
        ],
        'exchange_b' => [
            'type' => 'x-consistent-hash',
            'auto_delete' => false,
            'durable' => true,
            'internal' => true,
            'arguments' => ['hash-header' => 'user_id'],
        ],
    ],
    'queue' => [
        'queue_a' => [
            // The default values will be used here.
        ],
        'queue_b' => [
            'name' => ['queue_%N%', 10] // 10 queues will be created [queue_0, ..., queue_9]
            // Other values will be the default.
        ],
    ],
    'bind' => [
        ['exchange.exchange_a', 'exchange.exchange_b', 'create'],
        ['exchange.exchange_a', 'exchange.exchange_b', 'edit'],
        ['exchange.exchange_a', 'exchange.exchange_b', 'on'],
        ['exchange.exchange_a', 'exchange.exchange_b', 'off'],
        ['exchange.exchange_a', 'exchange.exchange_b', 'del'],
        ['queue.queue_a', 'exchange.exchange_a', 'create'],
        ['queue.queue_a', 'exchange.exchange_a', 'edit'],
        ['queue.queue_b', 'exchange.exchange_b', 2],
    ],
];

<?php
namespace PueueJobDispatcher;


use PueueJobDispatcher\Job\DispatchStrategy\Pueue;
use PueueJobDispatcher\Service\Job\DispatchStrategy\PueueFactory;

return [
    'service_manager' => [
        'factories' => [
            Pueue::class => PueueFactory::class,
        ],
        'aliases' => [
            'Omeka\Job\DispatchStrategy' => Pueue::class,
        ],
    ],
];

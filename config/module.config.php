<?php
namespace PueueJobDispatcher;

use Psr\Container\ContainerInterface;

return [
    'controllers' => [
        'factories' => [
        ],
    ],
    'form_elements' => [
        'factories' => [
            Form\ConfigForm::class => Service\Form\ConfigFormFactory::class,
        ],
    ],
    'pueuejobdispatcher' => [
        'config' => [
            'pueue_enabled' => 0,
            'pueue_path' => '',
            'pueue_group' => '',
        ],
    ],
];
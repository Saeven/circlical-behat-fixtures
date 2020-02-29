<?php

return [
    'circlical-fixtures' => [
        'excluded-tables' => [
//            'acl_roles',
//            'acl_actions',
        ],
    ],

    'service_manager' => [
        'factories' => [
            \CirclicalBehatFixtures\Command\FixturesLoadCommand::class => \CirclicalBehatFixtures\Factory\FixturesLoadCommandFactory::class,
        ]
    ]
];


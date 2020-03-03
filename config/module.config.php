<?php

return [
    'circlical-fixtures' => [

        // tables that should be excluded from purge
        'excluded-tables' => [
        ],

        // object classes that are in need of some magic  setter behavior, e.g. $object->id is private, and there is no ->setId(int $x)
        'auto-setters' => [
        ]
    ],

    'service_manager' => [
        'factories' => [
            \CirclicalBehatFixtures\Command\FixturesLoadCommand::class => \CirclicalBehatFixtures\Factory\FixturesLoadCommandFactory::class,
        ]
    ]
];


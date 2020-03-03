<?php

namespace CirclicalBehatFixtures\Factory;

use Doctrine\ORM\EntityManager;
use CirclicalBehatFixtures\Command\FixturesLoadCommand;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class FixturesLoadCommandFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');

        return new FixturesLoadCommand(
            $container->get(EntityManager::class),
            $config['circlical-fixtures']['excluded-tables'] ?? [],
            $config['circlical-fixtures']['auto-setters'] ?? []
        );
    }
}


<?php

namespace CirclicalBehatFixtures;

use Laminas\Loader\StandardAutoloader;
use Laminas\ModuleManager\Feature\AutoloaderProviderInterface;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\EventManager\EventInterface;
use Laminas\ModuleManager\ModuleManager;
use CirclicalBehatFixtures\Command\FixturesLoadCommand;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{
    public function getAutoloaderConfig(): array
    {
        return [
            StandardAutoloader::class => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ],
            ],
        ];
    }

    public function getConfig(): array
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    public function init(ModuleManager $moduleManager)
    {
        if ($events = $moduleManager->getEventManager()->getSharedManager()) {
            $events->attach('doctrine', 'loadCli.post', [$this, 'addFixturesLoadCommand']);
        }
    }

    public function addFixturesLoadCommand(EventInterface $event)
    {
        /* @var \Symfony\Component\Console\Application $application */
        $application = $event->getTarget();

        /* @var \Interop\Container\ContainerInterface $container */
        $application->add($event->getParam('ServiceManager')->get(FixturesLoadCommand::class));
    }
}
<?php

namespace CirclicalBehatFixtures\Loader;

use CirclicalBehatFixtures\Instantiator\PrivacySwitchInstantiator;
use Nelmio\Alice\Generator\Hydrator\Property\SymfonyPropertyAccessorHydrator;
use Nelmio\Alice\Generator\Hydrator\PropertyHydratorInterface;
use Nelmio\Alice\Loader\NativeLoader;

class ConfigurableLoader extends NativeLoader
{
    protected function createPropertyHydrator(): PropertyHydratorInterface
    {
        return new PrivacySwitchInstantiator(
            new SymfonyPropertyAccessorHydrator(
                $this->getPropertyAccessor()
            )
        );
    }
}
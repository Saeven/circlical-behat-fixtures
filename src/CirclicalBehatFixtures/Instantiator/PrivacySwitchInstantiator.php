<?php

namespace CirclicalBehatFixtures\Instantiator;

use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\Hydrator\PropertyHydratorInterface;
use Nelmio\Alice\ObjectInterface;

class PrivacySwitchInstantiator implements PropertyHydratorInterface
{
    private PropertyHydratorInterface $hydrator;

    public function __construct(PropertyHydratorInterface $defaultHydrator)
    {
        $this->hydrator = $defaultHydrator;
    }

    /**
     * Lazy hydrator, that will automatically rely on reflection if there is no setter for the property defined in the nelmio fixture
     */
    public function hydrate(ObjectInterface $object, Property $property, GenerationContext $context): ObjectInterface
    {
        $setterName = 'set' . $this->camelize($property->getName());
        $reflectionClass = new \ReflectionClass($object->getInstance());
        try {
            // if there's no camel case setter, than fall back onto reflection.  If someone conversely declares a private setter, it's their problem.
            $setter = $reflectionClass->getMethod($setterName);
        } catch (\ReflectionException $reflectionException) {
            $reflectionProperty = $reflectionClass->getProperty($property->getName());
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($object->getInstance(), $property->getValue());

            return $object;
        }

        return $this->hydrator->hydrate($object, $property, $context);
    }

    private function camelize(string $string): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }
}
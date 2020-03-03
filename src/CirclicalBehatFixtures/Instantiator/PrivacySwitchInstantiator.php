<?php

namespace CirclicalBehatFixtures\Instantiator;

use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\Hydrator\PropertyHydratorInterface;
use Nelmio\Alice\ObjectInterface;

class PrivacySwitchInstantiator implements PropertyHydratorInterface
{
    public static array $automaticSetters = [];

    private PropertyHydratorInterface $hydrator;

    public function __construct(PropertyHydratorInterface $defaultHydrator)
    {
        $this->hydrator = $defaultHydrator;
    }

    public function hydrate(ObjectInterface $object, Property $property, GenerationContext $context): ObjectInterface
    {
        $objectClass = get_class($object->getInstance());
        $setterName = 'set' . $this->camelize($property->getName());
        if (in_array($object, static::$automaticSetters, true)) {
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
        }

        return $this->hydrator->hydrate($object, $property, $context);
    }

    private function camelize(string $string): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }

}


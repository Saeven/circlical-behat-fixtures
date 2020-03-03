<?php

namespace CirclicalBehatFixtures\Loader;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Nelmio\Alice\Loader\NativeLoader;
use Symfony\Component\Console\Output\OutputInterface;

final class AliceFixtureLoader implements FixtureInterface
{
    /** @var NativeLoader */
    private static $nelmio;

    /** @var OutputInterface */
    private static $output;

    public static function createFromFilepath(string $file, bool $disableAutoIncrement, OutputInterface $output): AliceFixtureLoader
    {
        if (!static::$nelmio) {
            static::$nelmio = new ConfigurableLoader();
            static::$output = $output;
        }

        return new AliceFixtureLoader($file, $disableAutoIncrement);
    }

    private string $fixtureFile;

    private bool $disableAutoIncrement;

    private function __construct(string $file, bool $disableAutoIncrement)
    {
        $this->fixtureFile = $file;
        $this->disableAutoIncrement = $disableAutoIncrement;
    }

    public function load(ObjectManager $manager)
    {
        [$module, $file] = explode('/', $this->fixtureFile);
        $fixtureFile = sprintf('%s/module/%s/fixtures/%s.yml', getcwd(), $module, $file);
        static::$output->write('      - loading fixtures in ' . $fixtureFile, true);

        foreach (static::$nelmio->loadFile($fixtureFile)->getObjects() as $object) {
            if ($this->disableAutoIncrement) {
                $metadata = $manager->getClassMetadata(get_class($object));
                $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
            }
            $manager->persist($object);
        }
        $manager->flush();
    }
}
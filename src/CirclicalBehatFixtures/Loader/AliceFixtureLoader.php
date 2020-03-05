<?php

namespace CirclicalBehatFixtures\Loader;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Nelmio\Alice\Loader\NativeLoader;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

final class AliceFixtureLoader implements FixtureInterface
{
    /** @var NativeLoader */
    private static $nelmio;

    /** @var OutputInterface */
    private static $output;

    private static $instance;

    public static function getInstance(OutputInterface $output): AliceFixtureLoader
    {
        if (static::$instance === null) {
            static::$nelmio = new ConfigurableLoader();
            static::$output = $output;
            static::$instance = new AliceFixtureLoader();
        }

        return static::$instance;
    }

    private $fixtures;

    private function __construct()
    {
        $this->fixtures = [];
    }

    public function addFixtureId(string $fixtureId): void
    {
        $this->fixtures[] = $fixtureId;
    }

    public function load(ObjectManager $manager)
    {
        $fileList = [];
        foreach ($this->fixtures as $fixture) {
            [$module, $file] = explode('/', $fixture);
            $fixtureFile = sprintf('%s/module/%s/fixtures/%s.yml', getcwd(), $module, $file);
            static::$output->writeln(sprintf('  <fg=white;options=bold>   > loading file %s</>', $fixtureFile));
            $fileList[] = $fixtureFile;
        }

        $objectList = static::$nelmio->loadFiles($fileList)->getObjects();
        foreach ($objectList as $object) {
            /** @var ClassMetadataInfo $metadata */
            $metadata = $manager->getClassMetadata(get_class($object));
            static::$output->writeln(sprintf('  <fg=white>   %s── creating object of class %s</>', '└', $metadata->getName()));
            if ($metadata->usesIdGenerator()) {
                // scan each field to see if autogen member on the object is nonzero, if it was
                // then the intention was likely to set it manually rather than have it auto-generated
                $identifiersSet = false;
                foreach ($metadata->getIdentifierValues($object) as $field => $value) {
                    if ($value) {
                        $identifiersSet = [$field, $value];
                    }
                }
                $originalGeneratorType = null;
                if ($identifiersSet) {
                    $originalGeneratorType = $metadata->generatorType;
                    $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
                    static::$output->writeln(
                        sprintf(
                            '<fg=cyan;options=bold>         └ disabling autogen for object %s with %s of %s</>',
                            $metadata->getName(),
                            $identifiersSet[0],
                            $identifiersSet[1]
                        )
                    );
                }

                $manager->persist($object);
                $manager->flush();

                if (null !== $originalGeneratorType) {
                    $metadata->setIdGeneratorType($originalGeneratorType);
                }
                continue;
            }
            $manager->persist($object);
            $manager->flush();
        }
    }
}
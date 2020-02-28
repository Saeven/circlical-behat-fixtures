<?php

namespace CirclicalBehatFixtures\Loader;

use Doctrine\Persistence\ObjectManager;
use Nelmio\Alice\Loader\NativeLoader;
use Symfony\Component\Console\Output\OutputInterface;

final class AliceFixtureLoader implements \Doctrine\Common\DataFixtures\FixtureInterface
{
    /** @var NativeLoader */
    private static $nelmio;

    /** @var OutputInterface */
    private static $output;

    public static function createFromFilepath(string $file, OutputInterface $output): AliceFixtureLoader
    {
        if (!static::$nelmio) {
            static::$nelmio = new NativeLoader();
            static::$output = $output;
        }

        return new AliceFixtureLoader($file);
    }

    private string $fixtureFile;

    private function __construct(string $file)
    {
        $this->fixtureFile = $file;
    }

    public function load(ObjectManager $manager)
    {
        [$module, $file] = explode('/', $this->fixtureFile);
        $fixtureFile = sprintf('%s/module/%s/fixtures/%s.yml', getcwd(), $module, $file);
        static::$output->write('      - loading fixtures in ' . $fixtureFile, true);

        foreach (static::$nelmio->loadFile($fixtureFile)->getObjects() as $object) {
            $manager->persist($object);
        }
        $manager->flush();
    }
}
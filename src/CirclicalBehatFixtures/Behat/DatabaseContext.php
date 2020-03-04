<?php

namespace CirclicalBehatFixtures\Behat;

use Behat\Behat\Context\Context;

class DatabaseContext implements Context
{
    private $autoloader;

    private bool $appendFixture;

    private string $commandPrefix;

    public function __construct()
    {
        $this->appendFixture = false;
        $vendorFile = getcwd() . '/vendor/autoload.php';

        if (!file_exists($vendorFile)) {
            throw new \Exception("vendor/autoload.php could not be found.  Did you 'composer install'?");
        }

        $commandPrefixFile = getcwd() . DIRECTORY_SEPARATOR . 'circlical-fixtures-cmd-prefix';
        if (file_exists($commandPrefixFile)) {
            $this->commandPrefix = trim(file_get_contents($commandPrefixFile)) . ' ';
        }

        $this->autoloader = include $vendorFile;
    }

    /**
     * @Given Fixture :name is loaded
     */
    public function loadDoctrineFixture(string $fixtureName): void
    {
        shell_exec(
            sprintf(
                '%sphp public/index.php orm:fixtures:load --fixture=%s %s',
                $this->commandPrefix ?? '',
                $fixtureName,
                $this->getAppendParameter()
            )
        );
    }

    /**
     * @Given Fixture :name is loaded without auto-increment
     */
    public function loadDoctrineFixtureWithoutAutoincrement(string $fixtureName): void
    {
        shell_exec(
            sprintf(
                '%sphp public/index.php orm:fixtures:load --no-auto-increment --fixture=%s %s',
                $this->commandPrefix ?? '',
                $fixtureName,
                $this->getAppendParameter()
            )
        );
    }

    private function getAppendParameter(): string
    {
        $argument = '';
        if ($this->appendFixture) {
            $argument = '--append';
        }
        $this->appendFixture = true;

        return $argument;
    }
}

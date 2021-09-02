<?php

namespace CirclicalBehatFixtures\Behat;

use Behat\Behat\Context\Context;

class DatabaseContext implements Context
{
    private bool $appendFixture;

    private string $commandPrefix;

    public function __construct()
    {
        $this->appendFixture = false;
        $commandPrefixFile = getcwd() . DIRECTORY_SEPARATOR . 'circlical-fixtures-cmd-prefix';
        if (file_exists($commandPrefixFile)) {
            $this->commandPrefix = trim(file_get_contents($commandPrefixFile)) . ' ';
        }
    }

    /**
     * @Given Fixture :name is loaded
     */
    public function loadDoctrineFixture(string $fixtureName): void
    {
        shell_exec(
            sprintf(
                '%sphp vendor/bin/doctrine-module orm:fixtures:load --auto --fixtures=%s %s',
                $this->commandPrefix ?? '',
                $fixtureName,
                $this->getAppendParameter()
            )
        );
    }

    /**
     * @Given Fixtures :csv are loaded
     */
    public function loadDoctrineFixtureBatch(string $csv): void
    {
        shell_exec(
            sprintf(
                '%sphp vendor/bin/doctrine-module orm:fixtures:load --auto --fixtures=%s %s',
                $this->commandPrefix ?? '',
                str_replace(' ', '', $csv),
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

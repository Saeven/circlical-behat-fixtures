<?php

namespace CirclicalBehatFixtures\Behat;

use Behat\Behat\Context\Context;

class DatabaseContext implements Context
{
    private bool $appendFixture;
    private string $commandPrefix;
    private ?string $doctrineScript;

    public function __construct(string $doctrineScript = null)
    {
        $this->appendFixture = false;
        $commandPrefixFile = getcwd() . DIRECTORY_SEPARATOR . 'circlical-fixtures-cmd-prefix';
        if (is_file($commandPrefixFile)) {
            $this->commandPrefix = trim(file_get_contents($commandPrefixFile)) . ' ';
        }
        $this->doctrineScript = $doctrineScript;
    }

    /**
     * @Given Fixture :name is loaded
     */
    public function loadDoctrineFixture(string $fixtureName): void
    {
        shell_exec(
            sprintf(
                '%sphp %s orm:fixtures:load --auto --fixtures=%s %s',
                $this->commandPrefix ?? '',
                $this->doctrineScript ?? 'vendor/bin/doctrine-module',
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
                '%sphp %s orm:fixtures:load --auto --fixtures=%s %s',
                $this->commandPrefix ?? '',
                $this->doctrineScript ?? 'vendor/bin/doctrine-module',
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

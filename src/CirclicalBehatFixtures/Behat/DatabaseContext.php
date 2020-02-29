<?php

namespace CirclicalBehatFixtures\Behat;

use Behat\Behat\Context\Context;

class DatabaseContext implements Context
{
    private $autoloader;

    private bool $appendFixture;

    public function __construct()
    {
        $this->appendFixture = false;
        $vendorFile = getcwd() . '/vendor/autoload.php';

        if (!file_exists($vendorFile)) {
            throw new \Exception("vendor/autoload.php could not be found.  Did you 'composer install'?");
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
                'php public/index.php orm:fixtures:load --fixture=%s %s',
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
                'php public/index.php orm:fixtures:load --no-auto-increment --fixture=%s %s',
                $fixtureName,
                $this->getAppendParameter()
            )
        );
    }

    private function getAppendParameter(): string
    {
        $append = '';
        if ($this->appendFixture) {
            $append = '--append';
            $this->appendFixture = true;
        }

        return $append;
    }
}

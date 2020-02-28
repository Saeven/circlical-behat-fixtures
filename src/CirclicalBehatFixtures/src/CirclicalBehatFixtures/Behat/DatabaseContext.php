<?php

namespace CirclicalBehatFixtures\Behat;

use Behat\Behat\Context\Context;

/**
 * Defines application features from the specific context.
 */
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
        $append = '';
        if ($this->appendFixture) {
            $append = '--append';
            $this->appendFixture = true;
        }
        shell_exec(sprintf('php public/index.php orm:fixtures:load --fixture=%s %s', $fixtureName, $append));
    }
}

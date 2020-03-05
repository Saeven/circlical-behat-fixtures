# Simpler Behat Fixtures for Doctrine & Zend Framework

Trigger fixture loading from within Behat tests, like so:

```
Feature: I can define fixtures from within my tests
  In order to keep my test database clean
  As a developer writing behat tests
  I want to load fixtures directly from within my test context

  Scenario: Test loading a new fixture
    Given fixture "Application/user" is loaded
    Given fixture "#Application/user" is loaded
    Given fixtures "Application/user, #Billing/invoices" are loaded
```

> Disabling auto-increment modifies the class metadata before Doctrine saves the fixture.  I realize that auto-increment in databases shouldn't have significant
> meaning, but in the wild, this happens very often.  I've added this second command, but use it responsibly!  

Highlights:

- fixtures provided by the excellent [nelmio/alice](https://github.com/nelmio/alice)
- comes with a ready-to-go context you can plug into Behat
- define what fixtures are required right at the Scenario level
- adds a convenient CLI command as well

> Inspired by [dkorsak/doctrine-data-fixture-module](https://github.com/dkorsak/doctrine-data-fixture-module) -- thanks!

## Installation

- Add `CirclicalBehatFixtures` to your application.config.php

## Wiring it into Behat

- Edit your behat.yml, to add the `CirclicalBehatFixtures\Behat\DatabaseContext` context

e.g.

```
# behat.yml
default:
  autoload: [ '%paths.base%/../contexts' ]
  suites:
    core_features:
      paths: [ '%paths.base%/../features' ]
      contexts:
        - FeatureContext
        - CirclicalBehatFixtures\Behat\DatabaseContext
```

## Author a Fixture

The syntax for fixture identification is very simple, MODULE/fixturename. Examples:
- Application/user
- Application/roles
- Member/purchases

In your individual ZF modules, you will save your [nelmio/alice](https://github.com/nelmio/alice) fixtures as `module/MODULE/user.yml`.  Examples based on fixture IDs above:
- module/Application/fixtures/user.yml
- module/Application/fixtures/roles.yml
- module/Member/fixtures/purchases.yml

## Author a Scenario

Do this the way you usually would.  The context provided by this module gives you a new action:

`Given Fixture "FIXTUREID" is loaded`

e.g.

`Given Fixture "Application/user" is loaded`

You can stack these as you need.  The first one in a feature will auto-purge, the subsequent ones will append.

You can also instruct the scenario to disable auto-incrementing IDs by prefixing your fixture with a hash:

`Given Fixture "#Application/user" is loaded`

This also works in a multi-fixture situation:

`Given Fixtures "Application/users, #Application/widgets" are loaded`

In the case depicted above, only the second fixture would be loaded without auto-increment.

## Container Support

If you need to import the fixture in a Docker command for example, perhaps as a part of a CI/CD chain, you'll need to change where the fixture gets loaded.  In short, instead of this command:

    php public/index.php orm:fixtures:load --fixture=Application/orders

You might need to run something like this command:

    /usr/local/bin/docker container exec -w /code -i $(docker-compose ps -q php) php public/index.php orm:fixtures:load --fixture=Application/orders

You can achieve this by outputting a prefix into a file with name `circlical-fixtures-cmd-prefix`, e.g. in your CI/CD scripts:

    echo "/usr/local/bin/docker container exec -w /code -i $(docker-compose ps -q php) " > ./circlical-fixtures-cmd-prefix
    
It becomes your responsibility to create (at startup) and delete (at tear-down) this file in your CI chain configuration.
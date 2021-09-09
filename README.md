# Simpler Behat Fixtures for Doctrine & Laminas

Trigger fixture loading from within Behat tests, like so:

```
Feature: I can define fixtures from within my tests
  In order to keep my test database clean
  As a developer writing behat tests
  I want to load fixtures directly from within my test context

  Scenario: Test loading a new fixture
    Given fixture "Application/user" is loaded
    And fixture "Application/user" is loaded
    And fixtures "Application/user, Billing/invoices" are loaded
```

> This package adds a behavior to alice, by way of automatically falling back onto reflection
> when a setter for a fixture-defined value is not available, and that value is nontrivial.

Highlights:

- fixtures provided by the excellent [nelmio/alice](https://github.com/nelmio/alice)
- comes with a ready-to-go context you can plug into Behat
- define what fixtures are required right at the Scenario level
- adds a convenient CLI command as well

> Inspired by [dkorsak/doctrine-data-fixture-module](https://github.com/dkorsak/doctrine-data-fixture-module) -- thanks!

## Installation

- Composer install, then add `CirclicalBehatFixtures` to your application.config.php

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

### Or, if you need to override the location of the Doctrine bin (where the symlink points to)

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
          - vendor/doctrine/doctrine-module/bin/doctrine-module
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


## Container Support

If you need to import the fixture in a Docker command for example, perhaps as a part of a CI/CD chain, you'll need to change where the fixture gets loaded.  In short, instead of this command:

    vendor/bin/doctrine-module orm:fixtures:load --fixtures=Application/orders

You might need to run something like this command:

    /usr/local/bin/docker container exec -w /code -i $(docker-compose ps -q php) php vendor/bin/doctrine-module orm:fixtures:load --fixtures=Application/orders

You can achieve this by outputting a prefix into a file with name `circlical-fixtures-cmd-prefix`, e.g. in your CI/CD scripts:

    echo "/usr/local/bin/docker container exec -w /code -i $(docker-compose ps -q php) " > ./circlical-fixtures-cmd-prefix
    
It becomes your responsibility to create (at startup) and delete (at tear-down) this file in your CI chain configuration.
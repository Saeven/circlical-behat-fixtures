<?php

namespace CirclicalBehatFixtures\Command;

use CirclicalBehatFixtures\Instantiator\PrivacySwitchInstantiator;
use Doctrine\ORM\EntityManager;
use CirclicalBehatFixtures\Loader\AliceFixtureLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Loader as FixtureLoader;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class FixturesLoadCommand extends Command
{
    private array $excludedTables;

    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager, array $excludedTables)
    {
        parent::__construct();

        $this->excludedTables = $excludedTables;
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        parent::configure();

        $this->setName('orm:fixtures:load')
            ->setDescription('Load data fixtures to your database.')
            ->setHelp(
                <<<EOT
The command loads data from fixtures files into database for default <info>orm_default</info> connection:

  <info>./app/console doctrine:fixtures:load --fixtures Application/user</info>

If you want to append the fixtures instead of flushing the database first you can use the <info>--append</info> option:

  <info>./app/console doctrine:fixtures:load --append --fixtures Application/user</info>

By default Doctrine Data Fixtures uses DELETE statements to drop the existing rows from the database. 
If you want to use a TRUNCATE statement instead you can use the <info>--purge-with-truncate</info> flag:

  <info>./app/console doctrine:fixtures:load --purge-with-truncate --fixtures Application/user</info>
  
You can load a batch of fixtures, by pushing in a CSV of fixtures as fixtures parameter:

  <info>./app/console doctrine:fixtures:load --append --fixtures Application/user</info>
  
Lastly, you can specify that autoincrement should be disabled for a specific fixture by prefixing the fixture with a hash:

  <info>./app/console doctrine:fixtures:load --append --fixtures #Application/user</info>
  
In a batch, the hash's scope is limited to the fixture against which it is prepended:

  <info>./app/console doctrine:fixtures:load --append --fixtures #Application/user,Application/other</info>
  
EOT
            )
            ->addOption('fixtures', null, InputOption::VALUE_REQUIRED, 'The fixture files that we are loading, push in the fixture ID or a CSV of fixture IDs (see README.md)')
            ->addOption('append', null, InputOption::VALUE_NONE, 'Append the data fixtures instead of deleting all data from the database first.')
            ->addOption('purge-with-truncate', null, InputOption::VALUE_NONE, 'Purge data by using a database-level TRUNCATE statement')
            ->addOption('auto', null, InputOption::VALUE_NONE, 'Assume automatic mode, answer yes to all questions');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('auto') && $input->isInteractive() && !$input->getOption('append')
            && !$this->askConfirmation($input, $output, '<question>Careful, database will be purged. Do you want to continue y/N ?</question>', false)) {
            return;
        }

        $purger = new ORMPurger($this->entityManager, $this->excludedTables);
        $purgeMethod = $input->getOption('purge-with-truncate') ? ORMPurger::PURGE_MODE_TRUNCATE : ORMPurger::PURGE_MODE_DELETE;
        $purger->setPurgeMode($purgeMethod);

        $loader = new FixtureLoader();
        $circlicalLoader = AliceFixtureLoader::getInstance($output);
        $loader->addFixture($circlicalLoader);

        foreach (array_reverse(explode(',', $input->getOption('fixtures'))) as $fixtureId) {
            $output->writeln(sprintf('  <comment> -- Processing fixture file %s</comment>', $fixtureId));
            $circlicalLoader->addFixtureId($fixtureId);
        }

        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->setLogger(
            static function ($message) use ($output) {
                $output->writeln(sprintf('  <comment>></comment> <info>%s</info>', $message));
            }
        );
        $executor->execute($loader->getFixtures(), $input->getOption('append'));

        return 0;
    }

    private function askConfirmation(InputInterface $input, OutputInterface $output, string $question, bool $default): bool
    {
        $dialog = $this->getHelperSet()->get('dialog');
        $question = new ConfirmationQuestion($question, $default);

        return $dialog->ask($input, $output, $question);
    }
}
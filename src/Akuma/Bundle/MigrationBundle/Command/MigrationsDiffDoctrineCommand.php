<?php

namespace Akuma\Bundle\MigrationBundle\Command;

use Akuma\Bundle\MigrationBundle\Processor\SchemaDiffProcessor;
use Doctrine\Bundle\MigrationsBundle\Command\DoctrineCommand;
use Doctrine\Bundle\MigrationsBundle\Command\Helper\DoctrineCommandHelper;
use Doctrine\Bundle\MigrationsBundle\Command\MigrationsDiffDoctrineCommand as BaseMigrationsDiffDoctrineCommand;
use Doctrine\DBAL\Migrations\Provider\OrmSchemaProvider;
use Doctrine\DBAL\Migrations\Provider\SchemaProviderInterface;
use Doctrine\DBAL\Migrations\Tools\Console\Helper\MigrationDirectoryHelper;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaDiff;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\TableDiff;
use Doctrine\DBAL\Sharding\PoolingShardConnection;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AkumaMigrationsDiffDoctrineCommand
 * @package Akuma\Bundle\RetsBundle\Command
 * @author  Nikita Makarov <nikita.makarov@effective-soft.com>
 */
class MigrationsDiffDoctrineCommand extends BaseMigrationsDiffDoctrineCommand
{

    /** @var  ContainerInterface */
    protected $container;

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('akuma:migrations:diff')
            ->addOption('dbal', null, InputOption::VALUE_NONE, 'Use DBAL Format for migrations.');
    }

    /**
     * @param $name
     *
     * @return object
     */
    protected function get($name)
    {
        return $this->getContainer()->get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->executeDbal($input, $output);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function executeDbal(InputInterface $input, OutputInterface $output)
    {
        DoctrineCommandHelper::setApplicationEntityManager($this->getApplication(), $input->getOption('em'));

        if ($input->getOption('shard')) {
            $connection = $this->getApplication()->getHelperSet()->get('db')->getConnection();
            if (!$connection instanceof PoolingShardConnection) {
                throw new \LogicException(sprintf(
                    "Connection of EntityManager '%s' must implements shards configuration.",
                    $input->getOption('em')
                ));
            }

            $connection->connect($input->getOption('shard'));
        }

        $configuration = $this->getMigrationConfiguration($input, $output);
        DoctrineCommand::configureMigrations($this->getContainer(), $configuration);

        $conn = $configuration->getConnection();
        $platform = $conn->getDatabasePlatform();

        $fromSchema = $conn->getSchemaManager()->createSchema();
        $toSchema = $this->getSchemaProvider()->createSchema();
        $diffUp = $this->getSchemaDiff($fromSchema, $toSchema);

        $this->clearMigrationTable($diffUp, $configuration->getMigrationsTableName(), $platform);

        $diffDown = $this->getSchemaDiff($toSchema, $fromSchema);

        $this->clearMigrationTable($diffDown, $configuration->getMigrationsTableName(), $platform);

        $version = $configuration->generateVersionNumber();
        $s = new SchemaDiffProcessor();
        var_dump($s->process($diffUp));

        ///** @var \Symfony\Component\Templating\EngineInterface $templating */
        //$templating = $this->get('templating');
        //$x = $templating->render('@AkumaMigration/migration.html.twig', [
        //    'migrationVersion' => $version,
        //    'configuration' => $configuration,
        //    'up' => $diffUp,
        //    'down' => $diffDown,
        //]);
        //
        ////var_dump(preg_replace('/^ +$/m', '', $x));
        //$migrationDirectoryHelper = new MigrationDirectoryHelper($configuration);
        //$dir = $migrationDirectoryHelper->getMigrationDirectory();
        //$path = $dir . '/Version' . $version . '.php';
        //
        //file_put_contents($path, $x);
        //
        //$output->writeln(sprintf('Generated new migration class to "<info>%s</info>" from schema differences.', $path));
        //$output->writeln(file_get_contents($path));
    }

    /**
     * @param Schema $fromSchema
     * @param Schema $toSchema
     *
     * @return SchemaDiff
     */
    public function getSchemaDiff(Schema $fromSchema, Schema $toSchema)
    {
        $comparator = new Comparator();

        return $comparator->compare($fromSchema, $toSchema);
    }

    /**
     * @param SchemaDiff $diff
     * @param string $migrationTableName
     * @param AbstractPlatform $platform
     */
    public function clearMigrationTable(SchemaDiff $diff, $migrationTableName, AbstractPlatform $platform)
    {
        /** @var TableDiff $table */
        foreach ($diff->changedTables as $k => $table) {
            if ($table->getName($platform)->getName() === $migrationTableName) {
                unset($diff->changedTables[$k]);
            }
        }

        /** @var Table $table */
        foreach ($diff->newTables as $k => $table) {
            if ($table->getName() === $migrationTableName) {
                unset($diff->newTables[$k]);
            }
        }

        /** @var Table $table */
        foreach ($diff->removedTables as $k => $table) {
            if ($table->getName() === $migrationTableName) {
                unset($diff->removedTables[$k]);
            }
        }
    }

    /**
     * @return OrmSchemaProvider|SchemaProviderInterface
     */
    private function getSchemaProvider()
    {
        if (!$this->schemaProvider) {
            $this->schemaProvider = new OrmSchemaProvider($this->getHelper('entityManager')->getEntityManager());
        }

        return $this->schemaProvider;
    }

    /**
     * @return ContainerInterface
     */
    private function getContainer()
    {
        if (!$this->container) {
            $this->container = $this->getApplication()->getKernel()->getContainer();
        }

        return $this->container;
    }

}

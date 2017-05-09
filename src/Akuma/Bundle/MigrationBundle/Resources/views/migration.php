<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ColumnDiff;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaDiff;
use Doctrine\DBAL\Schema\Sequence;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\TableDiff;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version extends AbstractMigration
{
    /** @var SchemaDiff */
    private $diff;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema)
    {
        /** @var TableDiff $_table */
        foreach ($this->diff-> as $_table) {
            if ($_table->newName) {
                $name = $_table->newName;
                $schema->renameTable($_table->name, $_table->newName);
            } else {
                $name = $_table->name;
            }

            /** @var Table $table */
            $table = $schema->getTable($name);
            $table->add()

            /** @var ForeignKeyConstraint $f */
            $f;
            $f->getForeignTableName();
            $f->getCustomSchemaOptions();

            /** @var  \Doctrine\DBAL\Schema\Index $removedIndex */
            foreach ($_table-> as $removedIndex) {
                $table->dropIndex($removedIndex->getName());
            }

            /** @var  \Doctrine\DBAL\Schema\Column $removedColumn */
            foreach ($_table->removedColumns as $removedColumn) {
                $table->dropColumn($removedColumn->getName());
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function down(Schema $schema)
    {
    }
}

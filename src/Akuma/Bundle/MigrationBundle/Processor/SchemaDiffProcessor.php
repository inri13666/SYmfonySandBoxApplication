<?php

namespace Akuma\Bundle\MigrationBundle\Processor;

use Doctrine\DBAL\Schema\SchemaDiff;
use Doctrine\DBAL\Schema\Table;

/**
 * Class SchemaDiffProcessor
 * @package Akuma\Bundle\MigrationBundle\Processor
 * @author  Nikita Makarov <nikita.makarov@effective-soft.com>
 */
class SchemaDiffProcessor
{
    /**
     * @param SchemaDiff $diff
     *
     * @return string
     */
    public function process(SchemaDiff $diff)
    {
        $result = [];
        /** @var Table $table */
        foreach ($diff->newTables as $table) {
            $result = array_merge($result, $this->getTableProcessor()->process($table));
        };

        return $result;
    }

    /**
     * @return TableProcessor
     */
    protected function getTableProcessor()
    {
        return new TableProcessor();
    }
}

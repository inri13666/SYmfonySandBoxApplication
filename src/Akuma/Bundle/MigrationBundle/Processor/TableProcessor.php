<?php

namespace Akuma\Bundle\MigrationBundle\Processor;

use Doctrine\DBAL\Schema\Table;

/**
 * Class TableProcessor
 * @package Akuma\Bundle\MigrationBundle\Processor
 * @author  Nikita Makarov <nikita.makarov@effective-soft.com>
 */
class TableProcessor
{
    /**
     * @param Table $table
     *
     * @return array
     */
    public function process(Table $table)
    {
        $result = [];
        $result[] = sprintf('$table = $schema->createTable(\'%s\');\n', $table->getName());
        foreach ($table->getColumns() as $column) {
            $result = array_merge($result, $this->getColumnProcessor()->process($column));
        }

        foreach ($table->getIndexes() as $index) {
            if($index->isPrimary()){

            }else{
                $result = array_merge($result, $this->getIndexProcessor()->process($index));
            }
        }

        return $result;
    }

    /**
     * @return ColumnProcessor
     */
    protected function getColumnProcessor()
    {
        return new ColumnProcessor();
    }

    /**
     * @return IndexProcessor
     */
    protected function getIndexProcessor()
    {
        return new IndexProcessor();
    }
}

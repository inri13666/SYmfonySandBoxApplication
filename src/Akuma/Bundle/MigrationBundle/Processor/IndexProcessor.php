<?php

namespace Akuma\Bundle\MigrationBundle\Processor;

use Doctrine\DBAL\Schema\Index;

/**
 * Class IndexProcessor
 * @package Akuma\Bundle\MigrationBundle\Processor
 * @author  Nikita Makarov <nikita.makarov@effective-soft.com>
 */
class IndexProcessor
{
    /**
     * @param Index $index
     *
     * @return array
     */
    public function process(Index $index)
    {
        $result = [];
        $result[] = sprintf(
            '$table->addIndex(\'%s\', \'%s\', %s);\n',
            $index->getName(),
            strtolower($index->getName()),
            ''
        );

        return $result;
    }
}

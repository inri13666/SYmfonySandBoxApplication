<?php

namespace Akuma\Bundle\MigrationBundle\Processor;

use Doctrine\DBAL\Schema\Column;

/**
 * Class ColumnProcessor
 * @package Akuma\Bundle\MigrationBundle\Processor
 * @author  Nikita Makarov <nikita.makarov@effective-soft.com>
 */
class ColumnProcessor
{
    /**
     * @param Column $column
     *
     * @return array
     */
    public function process(Column $column)
    {
        $result = [];
        $result[] = sprintf(
            '$table->addColumn(\'%s\', \'%s\', %s);\n',
            $column->getName(),
            strtolower($column->getType()),
            $this->getParams($column)
        );

        return $result;
    }

    /**
     * @param Column $column
     *
     * @return string
     */
    private function getParams(Column $column)
    {
        $result = [];
        $params = array_filter($column->toArray());
        unset($params['name']);
        unset($params['type']);
        foreach ($params as $key => $value) {
            switch (true) {
                case is_bool($value):
                    $result[] = sprintf('\'%s\' => %s', $key, $value ? 'true' : 'false');
                    break;
                case is_numeric($value):
                    $result[] = sprintf('\'%s\' => %d', $key, $value);
                    break;
                default:
                    $result[] = sprintf('\'%s\' => \'%s\'', $key, $value);
                    break;
            }
        }

        return sprintf('[%s]', implode(', ', $result));
    }

}

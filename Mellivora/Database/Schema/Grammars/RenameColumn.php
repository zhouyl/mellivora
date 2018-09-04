<?php

namespace Mellivora\Database\Schema\Grammars;

use Doctrine\DBAL\Schema\AbstractSchemaManager as SchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\TableDiff;
use Mellivora\Database\Connection;
use Mellivora\Database\Schema\Blueprint;
use Mellivora\Support\Fluent;

class RenameColumn
{
    /**
     * Compile a rename column command.
     *
     * @param \Mellivora\Database\Schema\Grammars\Grammar $grammar
     * @param \Mellivora\Database\Schema\Blueprint        $blueprint
     * @param \Mellivora\Support\Fluent                   $command
     * @param \Mellivora\Database\Connection              $connection
     *
     * @return array
     */
    public static function compile(Grammar $grammar, Blueprint $blueprint, Fluent $command, Connection $connection)
    {
        $column = $connection->getDoctrineColumn(
            $grammar->getTablePrefix() . $blueprint->getTable(),
            $command->from
        );

        $schema = $connection->getDoctrineSchemaManager();

        return (array) $schema->getDatabasePlatform()->getAlterTableSQL(static::getRenamedDiff(
            $grammar,
            $blueprint,
            $command,
            $column,
            $schema
        ));
    }

    /**
     * Get a new column instance with the new column name.
     *
     * @param \Mellivora\Database\Schema\Grammars\Grammar $grammar
     * @param \Mellivora\Database\Schema\Blueprint        $blueprint
     * @param \Mellivora\Support\Fluent                   $command
     * @param \Doctrine\DBAL\Schema\Column                $column
     * @param \Doctrine\DBAL\Schema\AbstractSchemaManager $schema
     *
     * @return \Doctrine\DBAL\Schema\TableDiff
     */
    protected static function getRenamedDiff(Grammar $grammar, Blueprint $blueprint, Fluent $command, Column $column, SchemaManager $schema)
    {
        return static::setRenamedColumns(
            $grammar->getDoctrineTableDiff($blueprint, $schema),
            $command,
            $column
        );
    }

    /**
     * Set the renamed columns on the table diff.
     *
     * @param \Doctrine\DBAL\Schema\TableDiff $tableDiff
     * @param \Mellivora\Support\Fluent       $command
     * @param \Doctrine\DBAL\Schema\Column    $column
     *
     * @return \Doctrine\DBAL\Schema\TableDiff
     */
    protected static function setRenamedColumns(TableDiff $tableDiff, Fluent $command, Column $column)
    {
        $tableDiff->renamedColumns = [
            $command->from => new Column($command->to, $column->getType(), $column->toArray()),
        ];

        return $tableDiff;
    }
}

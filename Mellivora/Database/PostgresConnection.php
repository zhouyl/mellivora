<?php

namespace Mellivora\Database;

use Doctrine\DBAL\Driver\PDOPgSql\Driver as DoctrineDriver;
use Mellivora\Database\Query\Grammars\PostgresGrammar as QueryGrammar;
use Mellivora\Database\Query\Processors\PostgresProcessor;
use Mellivora\Database\Schema\Grammars\PostgresGrammar as SchemaGrammar;
use Mellivora\Database\Schema\PostgresBuilder;

class PostgresConnection extends Connection
{
    /**
     * Get the default query grammar instance.
     *
     * @return \Mellivora\Database\Query\Grammars\PostgresGrammar
     */
    protected function getDefaultQueryGrammar()
    {
        return $this->withTablePrefix(new QueryGrammar);
    }

    /**
     * Get a schema builder instance for the connection.
     *
     * @return \Mellivora\Database\Schema\PostgresBuilder
     */
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        return new PostgresBuilder($this);
    }

    /**
     * Get the default schema grammar instance.
     *
     * @return \Mellivora\Database\Schema\Grammars\PostgresGrammar
     */
    protected function getDefaultSchemaGrammar()
    {
        return $this->withTablePrefix(new SchemaGrammar);
    }

    /**
     * Get the default post processor instance.
     *
     * @return \Mellivora\Database\Query\Processors\PostgresProcessor
     */
    protected function getDefaultPostProcessor()
    {
        return new PostgresProcessor;
    }

    /**
     * Get the Doctrine DBAL driver.
     *
     * @return \Doctrine\DBAL\Driver\PDOPgSql\Driver
     */
    protected function getDoctrineDriver()
    {
        return new DoctrineDriver;
    }
}

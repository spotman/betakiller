<?php

abstract class Migration extends Kohana_Migration
{
    /**
     * @Inject
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    protected function runSql(string $sql, ?int $type = null, ?string $db = null): void
    {
        DB::query($type ?? Database::UPDATE, $sql)->execute($db);

        $this->logger->debug('SQL done: :query', [':query' => $sql]);
    }

    protected function tableExists(string $tableName, ?string $db = null): bool
    {
        return $this->tableHasColumn($tableName, '*', $db);
    }

    protected function tableHasColumn(string $tableName, string $columnName, ?string $db = null): bool
    {
        try {
            DB::select($columnName)->from($tableName)->limit(1)->execute($db)->as_array();

            // Query completed => table and column exists
            return true;
        }
        /** @noinspection BadExceptionsProcessingInspection */
        catch (Database_Exception $ignore) {
            // Query failed => table or column is absent
            return false;
        }
    }
}

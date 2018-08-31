<?php

abstract class Migration extends Kohana_Migration
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Migration constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

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

    protected function tableHasColumnValue(string $tableName, string $columnName, $value, ?string $db = null): bool
    {
        if (!$this->tableHasColumn($tableName, $columnName)) {
            return false;
        }

        $query = DB::select($columnName)
            ->from($tableName)
            ->where($columnName,'=', $value)
            ->limit(1);

        return (bool)$query->execute($db)->get($columnName);
    }
}

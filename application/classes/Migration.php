<?php

abstract class Migration extends Kohana_Migration
{
    /**
     * @Inject
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    protected function run_sql($sql, $type = null, $db = null)
    {
        DB::query($type ?? Database::UPDATE, $sql)->execute($db);

        $this->logger->debug('SQL done: :query', [':query' => $sql]);
    }

    protected function table_exists($table_name, $db = null)
    {
        return $this->table_has_column($table_name, null, $db);
    }

    protected function table_has_column($table_name, $column_name, $db = null)
    {
        try {
            DB::select($column_name)->from($table_name)->limit(1)->execute($db)->as_array();

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

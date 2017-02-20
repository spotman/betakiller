<?php defined('SYSPATH') or die('No direct access allowed.');

abstract class Migration extends Kohana_Migration
{
//    use BetaKiller\Helper\Base;
    use BetaKiller\Helper\LogTrait;

    protected function run_sql($sql, $type = Database::UPDATE, $db = NULL)
    {
        DB::query($type, $sql)->execute($db);

        $this->debug('SQL done: :query', [':query' => $sql]);
    }

    protected function table_exists($table_name, $db = NULL)
    {
        return $this->table_has_column($table_name, NULL, $db);
    }

    protected function table_has_column($table_name, $column_name, $db = NULL)
    {
        try
        {
            DB::select($column_name)->from($table_name)->limit(1)->execute($db)->as_array();

            // Query completed => table and column exists
            return TRUE;

        } catch (Database_Exception $e)
        {
            // Query failed => table or column is absent
            return FALSE;
        }
    }
}

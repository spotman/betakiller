<?php defined('SYSPATH') or die('No direct access allowed.');

abstract class Migration extends Kohana_Migration
{
    use BetaKiller\Helper\Base;

    protected function run_sql($sql, $type = Database::UPDATE, $db = NULL)
    {
        DB::query($type, $sql)->execute($db);
    }
}

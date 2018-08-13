<?php

class Migration1534170661_Move_Sqlite_Errors_Database_To_Logs extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id(): int
	{
		return 1534170661;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name(): string
	{
		return 'Move_sqlite_errors_database_to_logs';
	}

	/**
	 * Returns migration info
	 *
	 * @return string
	 */
	public function description(): string
	{
		return '';
	}

	/**
	 * Takes a migration
	 *
	 * @return void
	 */
	public function up(): void
	{
        $oldFile = $this->getOldFile();

        if (file_exists($oldFile)) {
            rename($oldFile, $this->getNewFile());
        }
    }

	/**
	 * Removes migration
	 *
	 * @return void
	 */
	public function down(): void
	{
        $newFile = $this->getNewFile();

        if (file_exists($newFile)) {
            rename($newFile, $this->getOldFile());
        }
	}

	private function getOldFile(): string
    {
        return implode(DIRECTORY_SEPARATOR, [realpath(MODPATH.'error'), 'media', 'errors.sqlite']);
    }

    /**
     * @return bool|string
     */
    private function getNewFile()
    {
        return realpath(APPPATH.'logs'.DIRECTORY_SEPARATOR.'errors.sqlite');
    }

} // End Migration1534170661_Move_Sqlite_Errors_Database_To_Logs

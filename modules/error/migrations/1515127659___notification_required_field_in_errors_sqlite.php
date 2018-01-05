<?php defined('SYSPATH') or die('No direct access allowed.');

class Migration1515127659_Notification_Required_Field_In_Errors_Sqlite extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id()
	{
		return 1515127659;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name()
	{
		return 'Notification_required_field_in_errors_sqlite';
	}

	/**
	 * Returns migration info
	 *
	 * @return string
	 */
	public function description()
	{
		return '';
	}

	/**
	 * Takes a migration
	 *
	 * @return void
	 */
	public function up()
	{
	    $sql = 'BEGIN; -- 0.000 s
CREATE TABLE "adminer_errors" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "hash" text NOT NULL,
  "urls" text NULL,
  "paths" text NULL,
  "modules" text NULL,
  "created_at" numeric NOT NULL,
  "last_seen_at" numeric NOT NULL,
  "last_notified_at" numeric NULL,
  "resolved_by" integer NULL,
  "status" text NOT NULL,
  "message" text NOT NULL,
  "notification_required" integer(1) NOT NULL DEFAULT 0,
  "total" integer NOT NULL DEFAULT 0
); -- 0.000 s
INSERT INTO "adminer_errors" ("id", "hash", "urls", "paths", "modules", "created_at", "last_seen_at", "last_notified_at", "resolved_by", "status", "message", "total") SELECT "id", "hash", "urls", "paths", "modules", "created_at", "last_seen_at", "last_notified_at", "resolved_by", "status", "message", "total" FROM "errors"; -- 0.000 s
DROP TABLE "errors"; -- 0.000 s
ALTER TABLE "adminer_errors" RENAME TO "errors"; -- 0.000 s
COMMIT; -- 0.003 s';

	    $this->run_sql($sql, Database::UPDATE, 'filesystem');
	}

	/**
	 * Removes migration
	 *
	 * @return void
	 */
	public function down()
	{

	}

} // End Migration1515127659_Notification_Required_Field_In_Errors_Sqlite

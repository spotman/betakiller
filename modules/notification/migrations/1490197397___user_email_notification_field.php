<?php defined('SYSPATH') or die('No direct access allowed.');

class Migration1490197397_User_Email_Notification_Field extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id()
	{
		return 1490197397;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name()
	{
		return 'User_email_notification_field';
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
	    if (!$this->table_has_column('users', 'notify_by_email')) {
	        $this->add_notify_by_email_field();
	        $this->update_notify_by_email_field_for_minion_user();
        }
	}

    protected function add_notify_by_email_field()
    {
        $this->run_sql("ALTER TABLE `users` ADD `notify_by_email` tinyint(1) unsigned NOT NULL DEFAULT '1';");
	}

    protected function update_notify_by_email_field_for_minion_user()
    {
        $this->run_sql("UPDATE `users` SET `notify_by_email` = 0 WHERE `username` = 'minion' LIMIT 1;");
	}

	/**
	 * Removes migration
	 *
	 * @return void
	 */
	public function down()
	{

	}

} // End Migration1490197397_User_Email_Notification_Field

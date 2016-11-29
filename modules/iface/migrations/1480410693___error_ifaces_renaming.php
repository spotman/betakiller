<?php defined('SYSPATH') or die('No direct access allowed.');

class Migration1480410693_Error_Ifaces_Renaming extends Migration {

	/**
	 * Returns migration ID
	 *
	 * @return integer
	 */
	public function id()
	{
		return 1480410693;
	}

	/**
	 * Returns migration name
	 *
	 * @return string
	 */
	public function name()
	{
		return 'Error_ifaces_renaming';
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

    protected function get_ifaces_list()
    {
        return [
            'Error_404' =>  'Error404',
            'Error_500' =>  'Error500',
        ];
	}

    protected function rename_ifaces($map)
    {
        foreach ($map as $old_codename => $new_codename)
        {
            DB::update('ifaces')
                ->value('codename', $new_codename)
                ->where('codename', '=', $old_codename)
                ->limit(1)
                ->execute();
        }
	}

	/**
	 * Takes a migration
	 *
	 * @return void
	 */
	public function up()
	{
	    $this->rename_ifaces($this->get_ifaces_list());
	}

	/**
	 * Removes migration
	 *
	 * @return void
	 */
	public function down()
	{
        $this->rename_ifaces(array_flip($this->get_ifaces_list()));
	}

} // End Migration1480410693_Error_Ifaces_Renaming

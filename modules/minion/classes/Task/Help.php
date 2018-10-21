<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Help task to display general instructons and list all tasks
 *
 * @package    Kohana
 * @category   Helpers
 * @author     Kohana Team
 * @copyright  (c) 2009-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Task_Help extends Minion_Task
{
	/**
	 * Generates a help list for all tasks
	 *
	 * @return void
	 */
	protected function _execute(array $params)
	{
		$tasks = $this->_compile_task_list(Kohana::list_files('classes/Task'));

		$view = View::factory('minion/help/list');

		$view->set('tasks', $tasks);

		echo $view->render();
	}
}

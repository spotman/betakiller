<?php

use BetaKiller\Model\UserInterface;

/**
 * Interface that all minion tasks must implement
 */
abstract class Minion_Task extends Kohana_Minion_Task
{
    use BetaKiller\Helper\LogTrait;

    const COLOR_RED        = Minion_CLI::RED;
    const COLOR_GREEN      = Minion_CLI::GREEN;
    const COLOR_BLUE       = Minion_CLI::BLUE;
    const COLOR_LIGHT_BLUE = Minion_CLI::LIGHT_BLUE;

    public function __construct()
    {
        $common_options = [
            'debug' => false,
            'stage' => 'development',
        ];

        $this->_options = array_merge($common_options, $this->_options, $this->define_options());

        parent::__construct();
    }

    protected function define_options()
    {
        return [];
    }

    protected static function _make_task_class_instance($class_name)
    {
        return \BetaKiller\DI\Container::getInstance()->get($class_name);
    }

    /**
     * Execute the task with the specified set of options
     *
     * @return null
     */
    public function execute()
    {
        $max_log_level = ($this->_options['debug'] !== false)
            ? Log::DEBUG
            : $this->get_max_log_level();

        $min_log_level = $this->get_min_log_level();

        Log::instance()->attach(new Minion_Log($max_log_level), $max_log_level, $min_log_level);

        return parent::execute();
    }

    /**
     *
     * Constant like Log::INFO
     *
     * @return int
     */
    protected function get_max_log_level()
    {
        return Log::INFO;
    }

    /**
     * Constant like Log::ALERT
     *
     * @return int
     */
    protected function get_min_log_level()
    {
        return Log::EMERGENCY;
    }

    /**
     * @param      $text
     * @param null $color
     *
     * @return $this
     */
    protected function write($text, $color = null)
    {
        if ($color) {
            $text = $this->colorize($text, $color);
        }

        Minion_CLI::write($text);

        return $this;
    }

    /**
     * @param        $text
     * @param bool   $eol
     * @param string $color
     *
     * @return $this
     */
    protected function write_replace($text, $eol = false, $color = null)
    {
        if ($color) {
            $text = $this->colorize($text, $color);
        }

        Minion_CLI::write_replace($text, $eol);

        return $this;
    }

    private function colorize($text, $fore, $back = null)
    {
        return Minion_CLI::color($text, $fore, $back);
    }

    /**
     * Get user input from CLI
     *
     * @param string $message
     * @param array  $options
     *
     * @return string
     */
    protected function read($message, array $options = null)
    {
        return Minion_CLI::read($message, $options);
    }

    /**
     * Get password user input from CLI
     *
     * @param $message
     *
     * @return string
     */
    protected function password($message)
    {
        return Minion_CLI::password($message);
    }
}

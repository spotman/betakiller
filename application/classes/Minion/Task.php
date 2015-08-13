<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Interface that all minion tasks must implement
 */
abstract class Minion_Task extends Kohana_Minion_Task {

    const RED           = Minion_CLI::RED;
    const GREEN         = Minion_CLI::GREEN;
    const BLUE          = Minion_CLI::BLUE;

    const LIGHT_BLUE    = Minion_CLI::LIGHT_BLUE;

    /**
     * Execute the task with the specified set of options
     *
     * @return null
     */
    public function execute()
    {
        $log_level = ( isset($this->_options['debug']) )
            ? Log::DEBUG
            : $this->get_max_log_level();

        Log::instance()->attach(new Minion_Log(), $log_level);

        parent::execute();
    }

    /**
     *
     * Constant like Log::INFO
     * @return int
     */
    protected function get_max_log_level()
    {
        return Log::INFO;
    }

    /**
     * @param $text
     * @param null $color
     */
    protected function write($text, $color = NULL)
    {
        if ($color)
            $text = $this->colorize($text, $color);

        Minion_CLI::write($text);
    }

    protected function write_replace($text, $eol = FALSE, $color = NULL)
    {
        if ($color)
            $text = $this->colorize($text, $color);

        Minion_CLI::write_replace($text, $eol);
    }

    protected function colorize($text, $fore, $back = NULL)
    {
        return Minion_CLI::color($text, $fore, $back);
    }

    protected function debug($message)
    {
        Log::debug($message);
    }

    protected function info($message)
    {
        Log::info($message);
    }

    protected function notice($message)
    {
        Log::notice($message);
    }

    protected function warning($message)
    {
        Log::warning($message);
    }

    protected function exception(Exception $e)
    {
        Log::exception($e);
    }

}

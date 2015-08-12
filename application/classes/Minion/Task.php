<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Interface that all minion tasks must implement
 */
abstract class Minion_Task extends Kohana_Minion_Task {

    const RED           = 'red';
    const GREEN         = 'green';
    const BLUE          = 'blue';
    const LIGHT_BLUE    = 'light_blue';

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

    protected function log_exception(Exception $e)
    {
        Kohana_Exception::log($e);
    }

}

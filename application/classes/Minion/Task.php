<?php

use BetaKiller\Task\TaskFactory;

/**
 * Interface that all minion tasks must implement
 */
abstract class Minion_Task extends Kohana_Minion_Task
{
    protected const COLOR_RED        = 'red';
    protected const COLOR_GREEN      = 'green';
    protected const COLOR_BLUE       = 'blue';
    protected const COLOR_LIGHT_BLUE = 'light_blue';

    /**
     * @param string $className
     *
     * @return \Minion_Task
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    protected static function _make_task_class_instance($className): Minion_Task
    {
        /** @var TaskFactory $factory */
        $factory = \BetaKiller\DI\Container::getInstance()->get(TaskFactory::class);

        return $factory->create($className);
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
    protected function writeReplace($text, ?bool $eol, $color = null)
    {
        if ($color) {
            $text = $this->colorize($text, $color);
        }

        Minion_CLI::write_replace($text, $eol ?? false);

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
    protected function read($message, array $options = null): string
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
    protected function password($message): string
    {
        return Minion_CLI::password($message);
    }
}

<?php

declare(strict_types=1);

namespace BetaKiller\Console;

use ReflectionClass;
use View;

class ConsoleHelper
{
    /**
     * The separator used to separate different levels of tasks
     *
     * @var string
     */
    public static string $task_separator = ':';

    /**
     * Outputs help for this task
     *
     * @param \BetaKiller\Console\ConsoleTaskInterface $task
     *
     * @return void
     * @throws \View_Exception
     */
    public static function displayHelp(ConsoleTaskInterface $task): void
    {
        $inspector = new ReflectionClass($task);

        [$description, $tags] = self::_parse_doccomment($inspector->getDocComment());

        $view = View::factory('minion/help/task')
            ->set('description', $description)
            ->set('tags', (array)$tags)
            ->set('task', self::convert_class_to_task($task));

        echo $view->render();
    }

    /**
     * Parses a doccomment, extracting both the comment and any tags associated
     *
     * Based on the code in Kodoc::parse()
     *
     * @param string The comment to parse
     *
     * @return array First element is the comment, second is an array of tags
     */
    private static function _parse_doccomment($comment)
    {
        // Normalize all new lines to \n
        $comment = str_replace(["\r\n", "\n"], "\n", $comment);

        // Remove the phpdoc open/close tags and split
        $comment = array_slice(explode("\n", $comment), 1, -1);

        // Tag content
        $tags = [];

        foreach ($comment as $i => $line) {
            // Remove all leading whitespace
            $line = preg_replace('/^\s*\* ?/m', '', $line);

            // Search this line for a tag
            if (preg_match('/^@(\S+)(?:\s*(.+))?$/', $line, $matches)) {
                // This is a tag line
                unset($comment[$i]);

                $name = $matches[1];
                $text = $matches[2] ?? '';

                $tags[$name] = $text;
            } else {
                $comment[$i] = (string)$line;
            }
        }

        $comment = trim(implode("\n", $comment));

        return [$comment, $tags];
    }

    /**
     * Compiles a list of available tasks from a directory structure
     *
     * @param array  $files Directory structure of tasks
     * @param string $prefix
     *
     * @return array Compiled tasks
     */
    public static function _compile_task_list(array $files, string $prefix = ''): array
    {
        $output = [];

        foreach ($files as $file => $path) {
            $file = substr($file, strrpos($file, DIRECTORY_SEPARATOR) + 1);

            if (is_array($path) and count($path)) {
                $task = self::_compile_task_list($path, $prefix.$file.self::$task_separator);

                if ($task) {
                    $output = array_merge($output, $task);
                }
            } else {
                $output[] = strtolower($prefix.substr($file, 0, -strlen(EXT)));
            }
        }

        return $output;
    }

    /**
     * Gets the task name of a task class / task object
     *
     * @param string|ConsoleTaskInterface $class The task class / object
     *
     * @return string             The task name
     */
    public static function convert_class_to_task(string|ConsoleTaskInterface $class): string
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        if (str_contains($class, '\\')) {
            $codename = explode('\\', $class);
            array_splice($codename, 0, -1 * \count($codename) + 2);
            $codename = implode(ConsoleHelper::$task_separator, $codename);
        } else {
            $codename = str_replace('_', ConsoleHelper::$task_separator, substr($class, 5));
        }

        return strtolower($codename);
    }

    /**
     * Converts a task (e.g. db:migrate to a class name)
     *
     * @param string $task Task name
     *
     * @return string Class name
     */
    public static function convert_task_to_class_name(string $task): string
    {
        return 'Task_'.implode('_', array_map('ucfirst', explode(ConsoleHelper::$task_separator, $task)));
    }

    /**
     * Returns one or more command-line options. Options are specified using
     * standard CLI syntax:
     *
     *     php index.php --username=john.smith --password=secret --var="some value with spaces"
     *
     *     // Get the values of "username" and "password"
     *     $auth = Minion_CLI::options('username', 'password');
     *
     * @return  array<string, string|bool|null>
     */
    public static function getRequestOptions(): array
    {
        // Found option values
        $values = [];

        // Skip the first option, it is always the file executed
        for ($i = 1; $i < $_SERVER['argc']; $i++) {
            if (!isset($_SERVER['argv'][$i])) {
                // No more args left
                break;
            }

            // Get the option
            $opt = $_SERVER['argv'][$i];

            if (!str_starts_with($opt, '--')) {
                // This is a positional argument
                $values[] = $opt;
                continue;
            }

            // Remove the "--" prefix
            $opt = substr($opt, 2);

            if (strpos($opt, '=')) {
                // Separate the name and value
                [$opt, $value] = explode('=', $opt, 2);
            } else {
                $value = true;
            }

            $values[$opt] = $value;
        }

        return $values;
    }
}

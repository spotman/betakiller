<?php

declare(strict_types=1);

namespace BetaKiller\Console;

use BetaKiller\View\ViewFactoryInterface;
use ReflectionClass;
use View;

use function count;

class ConsoleHelper
{
    /**
     * The separator used to separate different levels of tasks
     *
     * @var string
     */
    public static string $task_separator = ':';

    private static array $foreground_colors = [
        'black'        => '0;30',
        'dark_gray'    => '1;30',
        'blue'         => '0;34',
        'light_blue'   => '1;34',
        'green'        => '0;32',
        'light_green'  => '1;32',
        'cyan'         => '0;36',
        'light_cyan'   => '1;36',
        'red'          => '0;31',
        'light_red'    => '1;31',
        'purple'       => '0;35',
        'light_purple' => '1;35',
        'brown'        => '0;33',
        'yellow'       => '1;33',
        'light_gray'   => '0;37',
        'white'        => '1;37',
    ];

    private static string $wait_msg = 'Press any key to continue...';

    private static array $background_colors = [
        'black'      => '40',
        'red'        => '41',
        'green'      => '42',
        'yellow'     => '43',
        'blue'       => '44',
        'magenta'    => '45',
        'cyan'       => '46',
        'light_gray' => '47',
    ];


    /**
     * Outputs help for this task
     *
     * @param \BetaKiller\Console\ConsoleTaskInterface             $task
     * @param \BetaKiller\Console\ConsoleOptionCollectionInterface $options
     *
     * @return void
     * @throws \View_Exception
     */
    public static function displayTaskHelp(
        ConsoleTaskInterface $task,
        ConsoleOptionCollectionInterface $options,
        ViewFactoryInterface $viewFactory
    ): void {
        $details = [];

        foreach ($options as $option) {
            $details[] = [
                'name'     => $option->getName(),
                'type'     => $option->getType()->codename(),
                'label'    => $option->getLabel(),
                'required' => $option->isRequired() ? 'required' : 'optional',
                'default'  => $option->getDefaultValue(),
            ];
        }

        $view = $viewFactory->create('console/help/task')
            ->set('task', self::convert_class_to_task($task))
            ->set('options', $details);

        $phpDocs = (new ReflectionClass($task))->getDocComment();

        if ($phpDocs) {
            [$description, $tags] = self::_parse_doccomment($phpDocs);

            $view
                ->set('description', $description)
                ->set('tags', (array)$tags);
        }

        echo $view->render();
    }

    /**
     * Parses a doccomment, extracting both the comment and any tags associated
     *
     * Based on the code in Kodoc::parse()
     *
     * @param string $comment The comment to parse
     *
     * @return array First element is the comment, second is an array of tags
     */
    private static function _parse_doccomment(string $comment): array
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
                // Ignore abstract classes
                if (str_contains(mb_strtolower($path), 'abstract')) {
                    continue;
                }

                $output[] = $prefix.substr($file, 0, -strlen(EXT));
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
            array_splice($codename, 0, -1 * count($codename) + 2);
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
     *     $auth = ConsoleHelper::options('username', 'password');
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

    /**
     * Reads input from the user. This can have either 1 or 2 arguments.
     *
     * Usage:
     *
     * // Waits for any key press
     * ConsoleHelper::read();
     *
     * // Takes any input
     * $color = ConsoleHelper::read('What is your favorite color?');
     *
     * // Will only accept the options in the array
     * $ready = ConsoleHelper::read('Are you ready?', array('y','n'));
     *
     * @param string     $text    text to show user before waiting for input
     * @param array|null $options array of options the user is shown
     *
     * @return string  the user input
     */
    public static function read(string $text = '', array $options = null): string
    {
        // If a question has been asked with the read
        $options_output = '';
        if (!empty($options)) {
            $labels = array_is_list($options)
                ? array_values($options)
                : array_keys($options);

            $options_output = ' [ '.implode(', ', $labels).' ]';
        }

        fwrite(STDOUT, $text.$options_output.': ');

        // Read the input from keyboard.
        $input = trim(fgets(STDIN));

        // If options are provided and the choice is not in the array, tell them to try again
        if (!empty($options) && !in_array($input, $options)) {
            self::write('This is not a valid option. Please try again.');

            $input = self::read($text, $options);
        }

        // Read the input
        return $input;
    }

    /**
     * Returns the given text with the correct color codes for a foreground and
     * optionally a background color.
     *
     * @param string      $text       the text to color
     * @param string      $foreground the foreground color
     * @param string|null $background the background color
     *
     * @return string the color coded string
     * @throws \BetaKiller\Console\ConsoleException
     * @license    MIT License
     * @copyright  2010 - 2011 Fuel Development Team
     * @link       http://fuelphp.com
     * @author     Fuel Development Team
     */
    public static function color(string $text, string $foreground, string $background = null): string
    {
        if (!array_key_exists($foreground, self::$foreground_colors)) {
            throw new ConsoleException('Invalid CLI foreground color: '.$foreground);
        }

        if ($background !== null and !array_key_exists($background, self::$background_colors)) {
            throw new ConsoleException('Invalid CLI background color: '.$background);
        }

        $string = "\033[".self::$foreground_colors[$foreground]."m";

        if ($background !== null) {
            $string .= "\033[".self::$background_colors[$background]."m";
        }

        $string .= $text."\033[0m";

        return $string;
    }

    /**
     * Experimental feature.
     *
     * Reads hidden input from the user
     *
     * Usage:
     *
     * $password = ConsoleHelper::password('Enter your password');
     *
     * @param string $text
     *
     * @return string
     * @author Mathew Davies.
     */
    public static function password(string $text = ''): string
    {
        $text .= ': ';

        $password = shell_exec('/usr/bin/env bash -c \'read -s -p "'.escapeshellcmd($text).'" var && echo $var\'');

        self::write();

        return trim($password);
    }

    /**
     * Waits a certain number of seconds, optionally showing a wait message and
     * waiting for a key press.
     *
     * @param int  $seconds   number of seconds
     * @param bool $countdown show a countdown or not
     *
     * @copyright  2010 - 2011 Fuel Development Team
     * @link       http://fuelphp.com
     * @author     Fuel Development Team
     * @license    MIT License
     */
    public static function wait(int $seconds = 0, bool $countdown = false): void
    {
        if ($countdown === true) {
            $time = $seconds;

            while ($time > 0) {
                fwrite(STDOUT, $time.'... ');
                sleep(1);
                $time--;
            }

            self::write();
        } elseif ($seconds > 0) {
            sleep($seconds);
        } else {
            self::write(self::$wait_msg);
            self::read();
        }
    }

    /**
     * Outputs a string to the cli. If you send an array it will implode them
     * with a line break.
     *
     * @param string|array $text the text to output, or array of lines
     */
    public static function write(array|string $text = ''): void
    {
        if (is_array($text)) {
            foreach ($text as $line) {
                self::write($line);
            }
        } else {
            fwrite(STDOUT, $text.PHP_EOL);
        }
    }

    /**
     * Outputs a replaceable line to the cli. You can continue replacing the
     * line until `TRUE` is passed as the second parameter in order to indicate
     * you are done modifying the line.
     *
     *     // Sample progress indicator
     *     ConsoleHelper::writeReplace('0%');
     *     ConsoleHelper::writeReplace('25%');
     *     ConsoleHelper::writeReplace('50%');
     *     ConsoleHelper::writeReplace('75%');
     *     // Done writing this line
     *     ConsoleHelper::write_replace('100%', TRUE);
     *
     * @param string $text     the text to output
     * @param bool   $end_line whether the line is done being replaced
     */
    public static function writeReplace(string $text = '', bool $end_line = null): void
    {
        // Append a newline if $end_line is TRUE
        $text = $end_line ? $text.PHP_EOL : $text;
        fwrite(STDOUT, "\r\033[K".$text);
    }
}

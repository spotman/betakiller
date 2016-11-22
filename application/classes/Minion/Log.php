<?php defined('SYSPATH') OR die('No direct script access.');

class Minion_Log extends Log_Writer
{
    protected $_log_level;

    /**
     * Minion_Log constructor.
     * @param $log_level int
     */
    public function __construct($log_level = Log::INFO)
    {
        $this->_log_level = $log_level;

        // Disable original error messages
        ini_set('error_reporting', 'off');
    }

    /**
     * Write an array of messages.
     *
     *     $writer->write($messages);
     *
     * @param   array $messages
     * @return  void
     */
    public function write(array $messages)
    {
        foreach ($messages as $message)
        {
            $this->write_message($message);
        }
    }

    protected function write_message(array $message)
    {
        switch( $message['level'])
        {
            case Log::EMERGENCY:
            case Log::ALERT:
            case Log::CRITICAL:
            case Log::ERROR:
                $color = Minion_CLI::RED;
                $format = 'body in file:line';
                break;

            case Log::WARNING:
                $color = Minion_CLI::YELLOW;
                $format = 'body in file:line';
                break;

            case Log::NOTICE:
                $color = Minion_CLI::BROWN;
                $format = 'body in file:line';
                break;

            case Log::INFO:
                $color = Minion_CLI::GREEN;
                $format = 'body'; // level: body
                break;

            default:
                $color = Minion_CLI::LIGHT_GREY;
                $format = 'body'; // level: body
        }

        $text = $this->format_message($message, $format);

        Minion_CLI::write(
            Minion_CLI::color($text, $color)
        );
    }

    /**
     * Formats a log entry.
     *
     * @param   array   $message
     * @param   string  $format
     * @return  string
     */
    public function format_message(array $message, $format = "time --- level: body in file:line")
    {
        $message['time'] = Date::formatted_time('@'.$message['time'], Log_Writer::$timestamp, Log_Writer::$timezone);
        $message['level'] = $this->_log_levels[$message['level']];

        $string = strtr($format, array_filter($message, 'is_scalar'));

        // Add exception info if in debug mode
//        if ( $this->_log_level == Log::DEBUG AND isset($message['additional']['exception']))
        if ( isset($message['additional']['exception']))
        {
            /** @var Exception $exception */
            $exception = $message['additional']['exception'];

            // Re-use as much as possible, just resetting the body to the trace
            $message['body'] = $exception->getTraceAsString();
            $message['level'] = $this->_log_levels[Log_Writer::$strace_level];

            $string .= PHP_EOL.strtr($format, array_filter($message, 'is_scalar'));
        }

        return $string;
    }
}

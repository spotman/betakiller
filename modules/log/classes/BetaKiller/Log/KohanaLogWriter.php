<?php
namespace BetaKiller\Log;

class KohanaLogWriter extends \Log_Writer
{
    /**
     * Write an array of messages.
     *
     *     $writer->write($messages);
     *
     * @param   array $messages
     *
     * @return  void
     */
    public function write(array $messages)
    {
        $logger = Logger::getInstance();

        foreach ($messages as $message) {
            $level = $this->_log_levels[$message['level']];
//            $text = $this->format_message($message, 'body');
            $text = $message['body'];

            $logger->log($level, $text);
        }
    }
}

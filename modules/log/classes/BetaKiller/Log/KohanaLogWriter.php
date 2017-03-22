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
            $text = $message['body'];
            $context = [];

            /** @var \Exception $exception */
            $exception = isset($message['additional']['exception']) ? $message['additional']['exception'] : null;

            if ($exception) {
                $context['exception'] = $exception;
            }

            $logger->log($level, $text, $context);
        }
    }
}

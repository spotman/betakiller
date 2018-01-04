<?php
namespace BetaKiller\Log;

use Psr\Log\LoggerInterface;

class KohanaLogProxy extends \Log_Writer
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * KohanaLogProxy constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

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
        foreach ($messages as $message) {
            $level   = $this->_log_levels[$message['level']];
            $text    = $message['body'];
            $context = [];

            /** @var \Exception $exception */
            $exception = $message['additional']['exception'] ?? null;

            if ($exception) {
                $context['exception'] = $exception;
            }

            $this->logger->log($level, $text, $context);
        }
    }
}

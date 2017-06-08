<?php
namespace BetaKiller\Log;

use BetaKiller\DI\Container;
use Psr\Log\LoggerInterface;

class KohanaLogProxy extends \Log_Writer
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * KohanaLogProxy constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function register()
    {
        $writer = Container::getInstance()->get(self::class);

        // Proxy old Kohana logs to new logging subsystem
        \Kohana::$log->attach($writer);
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

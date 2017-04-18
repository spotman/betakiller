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

    public static function register()
    {
        // Proxy old Kohana logs to new logging subsystem
        \Kohana::$log->attach(new self());
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
            $level = $this->_log_levels[$message['level']];
            $text = $message['body'];
            $context = [];

            /** @var \Exception $exception */
            $exception = isset($message['additional']['exception']) ? $message['additional']['exception'] : null;

            if ($exception) {
                $context['exception'] = $exception;
            }

            $this->getLogger()->log($level, $text, $context);
        }
    }

    protected function getLogger()
    {
        if (!$this->logger) {
            $this->logger = Container::instance()->get(LoggerInterface::class);
        }

        return $this->logger;
    }
}

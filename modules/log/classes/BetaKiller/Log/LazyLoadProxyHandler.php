<?php
namespace BetaKiller\Log;

use BetaKiller\Exception;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\HandlerInterface;

class LazyLoadProxyHandler extends AbstractProcessingHandler
{
    /**
     * @var callable
     */
    private $factory;

    /**
     * @var \Monolog\Handler\HandlerInterface
     */
    private $handlerInstance;

    /**
     * LazyLoadProxyHandler constructor.
     *
     * @param callable $factory
     * @param int      $level  The minimum logging level at which this handler will be triggered
     * @param Boolean  $bubble Whether the messages that are handled can bubble up the stack or not
     *
     * @throws \BetaKiller\Exception
     */
    public function __construct(callable $factory, int $level, bool $bubble = true)
    {
        parent::__construct($level, $bubble);

        if (!\is_callable($factory)) {
            throw new Exception('Factory is not callable');
        }

        $this->factory = $factory;
    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  array $record
     *
     * @return void
     */
    protected function write(array $record): void
    {
        $this->getHandlerInstance()->handle($record);
    }

    private function getHandlerInstance(): HandlerInterface
    {
        if (!$this->handlerInstance) {
            $this->handlerInstance = \call_user_func($this->factory);
        }

        return $this->handlerInstance;
    }
}

<?php
declare(strict_types=1);

namespace BetaKiller\Task;

use BetaKiller\Wamp\WampRouter;

class RunWampRouter extends AbstractTask
{
    /**
     * @var \BetaKiller\Wamp\WampRouter
     */
    private $wampRouter;

    /**
     * @param \BetaKiller\Wamp\WampRouter $wampRouter
     */
    public function __construct(WampRouter $wampRouter)
    {
        $this->wampRouter = $wampRouter;
        parent::__construct();
    }

    /**
     * @return array
     */
    public function defineOptions(): array
    {
        return [];
    }

    public function run(): void
    {
        $this->wampRouter->run();
    }
}

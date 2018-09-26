<?php
declare(strict_types=1);

namespace BetaKiller\Task\Wamp;

use BetaKiller\Task\AbstractTask;
use BetaKiller\Wamp\WampRouter;

class RunWampRouter extends AbstractTask
{
    /**
     * @var \BetaKiller\Wamp\WampRouter
     */
    private $wampRouter;

    public function __construct(WampRouter $wampRouter)
    {
        $this->wampRouter = $wampRouter;
        parent::__construct();
    }

    public function defineOptions(): array
    {
        return [];
    }

    public function run(): void
    {
        $this->wampRouter->run();
    }
}

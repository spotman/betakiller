<?php

declare(strict_types=1);

namespace BetaKiller\Task\Cache;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Task\AbstractTask;
use Psr\SimpleCache\CacheInterface;

class Clear extends AbstractTask
{
    /**
     * @var \Psr\SimpleCache\CacheInterface
     */
    private $cache;

    /**
     * Task_Cache_Clear constructor.
     *
     * @param \Psr\SimpleCache\CacheInterface $cache
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        return [];
    }

    public function run(ConsoleInputInterface $params): void
    {
        $this->cache->clear();
    }
}

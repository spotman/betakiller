<?php
declare(strict_types=1);

namespace BetaKiller\Task\Cache;

use Psr\SimpleCache\CacheInterface;

class Clear extends \BetaKiller\Task\AbstractTask
{
    public function defineOptions(): array
    {
        return [];
    }

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

        parent::__construct();
    }

    public function run(): void
    {
        $this->cache->clear();
    }
}

<?php
declare(strict_types=1);

class Task_Cache_Clear extends \BetaKiller\Task\AbstractTask
{
    /**
     * @Inject
     * @var \Psr\SimpleCache\CacheInterface
     */
    private $cache;

    protected function _execute(array $params)
    {
        $this->cache->clear();
    }
}

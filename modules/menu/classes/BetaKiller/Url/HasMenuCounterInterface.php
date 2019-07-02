<?php
declare(strict_types=1);

namespace BetaKiller\Url;

use BetaKiller\Url\Container\UrlContainerInterface;

interface HasMenuCounterInterface
{
    /**
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @return int
     */
    public function getMenuCounter(UrlContainerInterface $params): int;
}

<?php
declare(strict_types=1);

namespace BetaKiller\Url;

use BetaKiller\Model\UserInterface;
use BetaKiller\Url\Container\UrlContainerInterface;

interface MenuCounterInterface
{
    public const NAMESPACES = ['MenuCounter'];
    public const SUFFIX     = 'MenuCounter';

    /**
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     * @param \BetaKiller\Model\UserInterface                 $user
     *
     * @return int
     */
    public function getMenuCounter(UrlContainerInterface $params, UserInterface $user): int;
}

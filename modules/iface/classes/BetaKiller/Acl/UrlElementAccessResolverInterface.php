<?php
declare(strict_types=1);

namespace BetaKiller\Acl;

use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\UrlElementInterface;
use Spotman\Acl\AclUserInterface;

interface UrlElementAccessResolverInterface
{
    public function isAllowed(
        AclUserInterface $user,
        UrlElementInterface $urlElement,
        ?UrlContainerInterface $params = null
    ): bool;
}

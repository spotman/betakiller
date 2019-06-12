<?php
declare(strict_types=1);

namespace BetaKiller\Action;

use BetaKiller\Url\UrlElementInstanceInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface ActionInterface extends RequestHandlerInterface, UrlElementInstanceInterface
{
    public const NAMESPACE = 'Action';
    public const SUFFIX    = 'Action';
}

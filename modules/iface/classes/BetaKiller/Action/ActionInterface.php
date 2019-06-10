<?php
declare(strict_types=1);

namespace BetaKiller\Action;

use Psr\Http\Server\RequestHandlerInterface;

interface ActionInterface extends RequestHandlerInterface
{
    public const NAMESPACE = 'Action';
    public const SUFFIX    = 'Action';
}

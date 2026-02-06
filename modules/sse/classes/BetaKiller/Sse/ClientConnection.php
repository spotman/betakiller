<?php

declare(strict_types=1);

namespace BetaKiller\Sse;

use BetaKiller\Helper\SessionHelper;
use Mezzio\Session\SessionInterface;
use React\Stream\ThroughStream;

final readonly class ClientConnection
{
    public function __construct(public SessionInterface $session, public ThroughStream $stream)
    {
    }

    public function getId(): string
    {
        return SessionHelper::getId($this->session);
    }
}

<?php
declare(strict_types=1);

namespace BetaKiller\WebHook\Test;

use Psr\Http\Message\ServerRequestInterface;

class DummySucceeded extends AbstractDummyWebHook
{
    public function process(ServerRequestInterface $request): void
    {
    }
}

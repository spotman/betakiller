<?php
declare(strict_types=1);

namespace BetaKiller\Helper;

use Psr\Http\Message\ServerRequestInterface;

class ServerRequestHelper
{
    public function getUserAgent(ServerRequestInterface $request): ?string
    {
        $serverParams = $request->getServerParams();

        return $serverParams['HTTP_USER_AGENT'] ?? null;
    }
}

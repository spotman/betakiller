<?php

declare(strict_types=1);

namespace BetaKiller\IFace\Auth;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\IFace\AbstractIFace;
use Psr\Http\Message\ServerRequestInterface;

readonly class BlockedIFace extends AbstractIFace
{
    /**
     * BlockedIFace constructor.
     *
     * @param \BetaKiller\Config\AppConfigInterface $appConfig
     */
    public function __construct(private AppConfigInterface $appConfig)
    {
    }

    /**
     * Returns data for View
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     */
    public function getData(ServerRequestInterface $request): array
    {
        // No data
        return [
            'contact_url' => $this->appConfig->getSupportUrl(),
        ];
    }
}

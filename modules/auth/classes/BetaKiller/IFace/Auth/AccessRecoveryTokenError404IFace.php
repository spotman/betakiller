<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Auth;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\AbstractIFace;
use Psr\Http\Message\ServerRequestInterface;

class AccessRecoveryTokenError404IFace extends AbstractIFace
{
    /**
     * Returns data for View
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     */
    public function getData(ServerRequestInterface $request): array
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        $recoveryElement = $urlHelper->getUrlElementByCodename(AccessRecoveryRequestIFace::codename());

        return [
            'request_url' => $urlHelper->makeUrl($recoveryElement, null, false),
        ];
    }
}

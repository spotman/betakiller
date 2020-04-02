<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Auth;

use BetaKiller\Action\Auth\ActivateSuspendedAction;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\AbstractIFace;
use Psr\Http\Message\ServerRequestInterface;

class SuspendedIFace extends AbstractIFace
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
        $helper = ServerRequestHelper::getUrlHelper($request);

        return [
            'activate_url' => $helper->makeCodenameUrl(ActivateSuspendedAction::codename()),
        ];
    }
}

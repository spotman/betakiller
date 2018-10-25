<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Admin\Test;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\AbstractIFace;
use Psr\Http\Message\ServerRequestInterface;

class WampRpcManager extends AbstractIFace
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return string[]
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getData(ServerRequestInterface $request): array
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        $testElement = $urlHelper->getUrlElementByCodename(WampRpcRunner::codename());
        $testUrl     = $urlHelper->makeUrl($testElement, null, false);

        $userAgent = ServerRequestHelper::getUserAgent($request);

        return [
            'userAgent' => $userAgent,
            'testUrl'   => $testUrl,
        ];
    }
}

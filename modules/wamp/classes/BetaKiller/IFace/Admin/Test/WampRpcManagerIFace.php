<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Admin\Test;

use BetaKiller\Api\Method\WampTest\DataApiMethod;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\AbstractIFace;
use Psr\Http\Message\ServerRequestInterface;

class WampRpcManagerIFace extends AbstractIFace
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return string[]
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function getData(ServerRequestInterface $request): array
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        $testElement = $urlHelper->getUrlElementByCodename(WampRpcRunnerIFace::codename());
        $testUrl     = $urlHelper->makeUrl($testElement, null, false);

        $userAgent = ServerRequestHelper::getUserAgent($request);

        return [
            'userAgent' => $userAgent,
            'testUrl'   => $testUrl,
            'cases'     => DataApiMethod::AVAILABLE_CASES,
        ];
    }
}

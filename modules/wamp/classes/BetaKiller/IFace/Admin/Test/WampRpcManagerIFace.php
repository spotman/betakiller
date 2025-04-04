<?php

declare(strict_types=1);

namespace BetaKiller\IFace\Admin\Test;

use BetaKiller\Api\Method\WampTest\DataApiMethod;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\AbstractIFace;
use Psr\Http\Message\ServerRequestInterface;

readonly class WampRpcManagerIFace extends AbstractIFace
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
        $userAgent = ServerRequestHelper::getUserAgent($request);

        return [
            'userAgent' => $userAgent,
            'testUrl'   => $urlHelper->makeCodenameUrl(WampRpcRunnerIFace::codename(), null, false),
            'cases'     => DataApiMethod::AVAILABLE_CASES,
        ];
    }
}

<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Admin\Tests;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\AbstractIFace;
use Psr\Http\Message\ServerRequestInterface;

class TestWampRpcManager extends AbstractIFace
{
    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    private $request;

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return string[]
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getData(ServerRequestInterface $request): array
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        $testElement = $urlHelper->getUrlElementByCodename(TestWampRpcTest::codename());
        $testUrl     = $urlHelper->makeUrl($testElement, null, false);

        $userAgent = ServerRequestHelper::getUserAgent($request);

        return [
            'userAgent' => $userAgent,
            'testUrl'   => $testUrl,
        ];
    }
}

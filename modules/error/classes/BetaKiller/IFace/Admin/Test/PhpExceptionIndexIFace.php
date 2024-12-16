<?php

namespace BetaKiller\IFace\Admin\Test;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\Admin\Error\AbstractErrorAdminIFace;
use Psr\Http\Message\ServerRequestInterface;

readonly class PhpExceptionIndexIFace extends AbstractErrorAdminIFace
{
    /**
     * Returns data for View
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     * @throws \BetaKiller\Url\UrlElementException
     * @uses \BetaKiller\IFace\Admin\Test\PhpExceptionHttp500IFace
     * @uses \BetaKiller\IFace\Admin\Test\PhpExceptionLoggerIFace
     */
    public function getData(ServerRequestInterface $request): array
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        return [
            'http_500_test_url' => $urlHelper->makeCodenameUrl(PhpExceptionHttp500IFace::codename()),
            'logger_test_url'   => $urlHelper->makeCodenameUrl(PhpExceptionLoggerIFace::codename()),
        ];
    }
}

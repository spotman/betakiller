<?php
namespace BetaKiller\IFace\Admin\Error;

use BetaKiller\Helper\ServerRequestHelper;
use Psr\Http\Message\ServerRequestInterface;

class PhpExceptionTest extends ErrorAdminBase
{
    /**
     * Returns data for View
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @uses \BetaKiller\IFace\Admin\Error\PhpExceptionTestHTTP500
     * @uses \BetaKiller\IFace\Admin\Error\PhpExceptionTestLogger
     */
    public function getData(ServerRequestInterface $request): array
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        $http500IFace = $urlHelper->getUrlElementByCodename('Admin_Error_PhpExceptionTestHTTP500');

        $loggerIFace = $urlHelper->getUrlElementByCodename('Admin_Error_PhpExceptionTestLogger');

        return [
            'http_500_test_url' => $urlHelper->makeUrl($http500IFace),
            'logger_test_url'   => $urlHelper->makeUrl($loggerIFace),
        ];
    }
}

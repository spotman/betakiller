<?php
namespace BetaKiller\IFace\Admin\Test;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\Admin\Error\ErrorAdminBase;
use Psr\Http\Message\ServerRequestInterface;

class PhpExceptionIndexIFace extends ErrorAdminBase
{
    /**
     * Returns data for View
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @uses \BetaKiller\IFace\Admin\Test\PhpExceptionHttp500IFace
     * @uses \BetaKiller\IFace\Admin\Test\PhpExceptionLoggerIFace
     */
    public function getData(ServerRequestInterface $request): array
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        $http500IFace = $urlHelper->getUrlElementByCodename('Admin_Test_PhpExceptionHttp500');
        $loggerIFace  = $urlHelper->getUrlElementByCodename('Admin_Test_PhpExceptionLogger');

        return [
            'http_500_test_url' => $urlHelper->makeUrl($http500IFace),
            'logger_test_url'   => $urlHelper->makeUrl($loggerIFace),
        ];
    }
}

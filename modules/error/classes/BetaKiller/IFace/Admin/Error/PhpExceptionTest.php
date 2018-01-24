<?php
namespace BetaKiller\IFace\Admin\Error;

class PhpExceptionTest extends ErrorAdminBase
{
    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function getData(): array
    {
        /** @var \BetaKiller\IFace\Admin\Error\PhpExceptionTestHTTP500 $http500IFace */
        $http500IFace = $this->ifaceHelper->createIFaceFromCodename('Admin_Error_PhpExceptionTestHTTP500');

        /** @var \BetaKiller\IFace\Admin\Error\PhpExceptionTestLogger $loggerIFace */
        $loggerIFace = $this->ifaceHelper->createIFaceFromCodename('Admin_Error_PhpExceptionTestLogger');

        return [
            'http_500_test_url' => $this->ifaceHelper->makeIFaceUrl($http500IFace),
            'logger_test_url'   => $this->ifaceHelper->makeIFaceUrl($loggerIFace),
        ];
    }
}

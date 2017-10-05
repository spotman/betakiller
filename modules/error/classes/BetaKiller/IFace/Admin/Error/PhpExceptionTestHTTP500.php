<?php
namespace BetaKiller\IFace\Admin\Error;

class PhpExceptionTestHTTP500 extends ErrorAdminBase
{
    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     * @throws \HTTP_Exception_500
     */
    public function getData(): array
    {
        throw new \HTTP_Exception_500('This is a test');
    }
}

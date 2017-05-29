<?php
namespace BetaKiller\Helper;

class PhpExceptionUrlParametersHelper extends UrlParametersHelper
{
    /**
     * @return \Model_PhpException|null
     */
    public function getPhpException()
    {
        return $this->getEntityByClassName(\Model_PhpException::class);
    }
}

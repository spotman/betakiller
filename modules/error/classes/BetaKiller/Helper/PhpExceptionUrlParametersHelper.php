<?php
namespace BetaKiller\Helper;

class PhpExceptionUrlParametersHelper extends UrlParametersHelper
{
    /**
     * @return \BetaKiller\Model\PhpException|null
     */
    public function getPhpException()
    {
        return $this->getEntityByClassName(\BetaKiller\Model\PhpException::class);
    }
}

<?php
namespace BetaKiller\Helper;

class PhpExceptionUrlContainerHelper extends UrlContainerHelper
{
    /**
     * @return \BetaKiller\Model\PhpException|null
     */
    public function getPhpException()
    {
        return $this->getEntityByClassName(\BetaKiller\Model\PhpException::class);
    }
}

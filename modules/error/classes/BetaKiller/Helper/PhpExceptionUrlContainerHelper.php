<?php
namespace BetaKiller\Helper;

use BetaKiller\Model\PhpException;

class PhpExceptionUrlContainerHelper extends UrlContainerHelper
{
    /**
     * @return \BetaKiller\Model\PhpException|null
     */
    public function getPhpException(): ?PhpException
    {
        return $this->getEntityByClassName(PhpException::class);
    }
}

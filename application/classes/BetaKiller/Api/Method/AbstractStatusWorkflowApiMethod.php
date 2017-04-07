<?php
namespace BetaKiller\Api\Method;

use BetaKiller\Api\AccessResolver\StatusWorkflowApiMethodAccessResolver;
use Spotman\Api\Method\AbstractModelBasedApiMethod;

abstract class AbstractStatusWorkflowApiMethod extends AbstractModelBasedApiMethod
{
    /**
     * @return string
     */
    public function getAccessResolverName()
    {
        return StatusWorkflowApiMethodAccessResolver::CODENAME;
    }
}

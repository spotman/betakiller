<?php
namespace BetaKiller\Api\AccessResolver;

use BetaKiller\Status\StatusRelatedModelInterface;
use Spotman\Api\AccessResolver\DefaultApiMethodAccessResolverDetector;
use Spotman\Api\ApiMethodInterface;
use Spotman\Api\Method\ModelBasedApiMethodInterface;

class CustomApiMethodAccessResolverDetector extends DefaultApiMethodAccessResolverDetector
{
    /**
     * @param \Spotman\Api\ApiMethodInterface $method
     *
     * @return string AccessResolver codename
     */
    public function detect(ApiMethodInterface $method)
    {
        if ($this->isStatusRelatedMethod($method)) {
            return StatusRelatedModelApiMethodAccessResolver::CODENAME;
        }

        return parent::detect($method);
    }

    private function isStatusRelatedMethod(ApiMethodInterface $method)
    {
        if (!($method instanceof ModelBasedApiMethodInterface)) {
            return false;
        }

        $model = $method->getModel();

        if (!($model instanceof StatusRelatedModelInterface)) {
            return false;
        }

        if (!$model->get_current_status()->isStatusAclEnabled()) {
            return false;
        }

        return true;
    }
}

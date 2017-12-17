<?php
namespace BetaKiller\Api\AccessResolver;

use BetaKiller\Api\Method\EntityBasedApiMethodInterface;
use Spotman\Api\AccessResolver\DefaultApiMethodAccessResolverDetector;
use Spotman\Api\ApiMethodInterface;

class CustomApiMethodAccessResolverDetector extends DefaultApiMethodAccessResolverDetector
{
    /**
     * @param \Spotman\Api\ApiMethodInterface $method
     *
     * @return string AccessResolver codename
     */
    public function detect(ApiMethodInterface $method): string
    {
        if ($this->isEntityRelatedMethod($method)) {
            return EntityRelatedAclApiMethodAccessResolver::CODENAME;
        }

        return parent::detect($method);
    }

    private function isEntityRelatedMethod(ApiMethodInterface $method)
    {
        return ($method instanceof EntityBasedApiMethodInterface);
    }
}

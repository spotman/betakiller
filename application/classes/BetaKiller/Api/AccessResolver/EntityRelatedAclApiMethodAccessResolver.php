<?php
namespace BetaKiller\Api\AccessResolver;

use BetaKiller\Api\Method\EntityBasedApiMethodInterface;
use BetaKiller\Status\StatusRelatedModelInterface;
use Spotman\Api\AccessResolver\AclApiMethodAccessResolver;
use Spotman\Api\ApiMethodException;
use Spotman\Api\ApiMethodInterface;

class EntityRelatedAclApiMethodAccessResolver extends AclApiMethodAccessResolver
{
    const CODENAME = 'EntityRelatedAcl';

    protected function getAclResourceFromApiMethod(ApiMethodInterface $method)
    {
        if (!($method instanceof EntityBasedApiMethodInterface)) {
            throw new ApiMethodException('Api method [:collection.:method] must implement :interface', [
                ':collection' => $method->getCollectionName(),
                ':method'     => $method->getName(),
                ':interface'  => StatusRelatedModelInterface::class,
            ]);
        }

        /** @var \BetaKiller\Acl\Resource\EntityRelatedAclResourceInterface $resource */
        $resource = parent::getAclResourceFromApiMethod($method);

        $entity = $method->getEntity();

        // Store model for processing status and transition permissions
        $resource->setEntity($entity);

        return $resource;
    }
}

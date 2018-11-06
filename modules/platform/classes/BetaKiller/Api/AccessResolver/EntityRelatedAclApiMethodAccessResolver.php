<?php
namespace BetaKiller\Api\AccessResolver;

use BetaKiller\Api\Method\EntityBasedApiMethodInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Status\StatusRelatedModelInterface;
use Spotman\Acl\Resource\ResolvingResourceInterface;
use Spotman\Api\AccessResolver\AclApiMethodAccessResolver;
use Spotman\Api\ApiMethodException;
use Spotman\Api\ApiMethodInterface;
use Spotman\Api\ArgumentsInterface;

class EntityRelatedAclApiMethodAccessResolver extends AclApiMethodAccessResolver
{
    public const CODENAME = 'EntityRelatedAcl';

    protected function prepareResource(
        ResolvingResourceInterface $resource,
        ApiMethodInterface $method,
        ArgumentsInterface $arguments,
        UserInterface $user
    ): void {
        if (!($method instanceof EntityBasedApiMethodInterface)) {
            throw new ApiMethodException('Api method [:collection.:method] must implement :interface', [
                ':collection' => $method->getCollectionName(),
                ':method'     => $method->getName(),
                ':interface'  => StatusRelatedModelInterface::class,
            ]);
        }

        parent::prepareResource($resource, $method, $arguments, $user);

        /** @var \BetaKiller\Acl\Resource\EntityRelatedAclResourceInterface $resource */
        $resource = parent::getAclResourceFromApiMethod($method);

        $entity = $method->getEntity($arguments);

        // Store model for processing status and transition permissions
        $resource->setEntity($entity);
    }
}

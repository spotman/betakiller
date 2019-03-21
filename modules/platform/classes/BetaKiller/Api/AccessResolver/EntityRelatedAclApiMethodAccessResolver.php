<?php
namespace BetaKiller\Api\AccessResolver;

use BetaKiller\Acl\Resource\EntityRelatedAclResourceInterface;
use BetaKiller\Api\Method\EntityBasedApiMethodInterface;
use BetaKiller\Model\UserInterface;
use Spotman\Acl\Resource\ResolvingResourceInterface;
use Spotman\Api\AccessResolver\AclApiMethodAccessResolver;
use Spotman\Api\ApiMethodException;
use Spotman\Api\ApiMethodInterface;
use Spotman\Defence\ArgumentsInterface;

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
                ':interface'  => EntityBasedApiMethodInterface::class,
            ]);
        }

        parent::prepareResource($resource, $method, $arguments, $user);

        $resource = $this->getAclResourceFromApiMethod($method);

        if (!$resource instanceof EntityRelatedAclResourceInterface) {
            throw new ApiMethodException('Acl resource for Api collection ":collection" must implement :interface', [
                ':collection' => $method->getCollectionName(),
                ':interface'  => EntityRelatedAclResourceInterface::class,
            ]);
        }

        if ($resource->isEntityRequiredForAction($method->getName())) {
            $entity = $method->getEntity($arguments);

            // Store model for processing status and transition permissions
            $resource->setEntity($entity);
        }
    }
}

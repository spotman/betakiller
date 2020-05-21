<?php
namespace BetaKiller\Api\AccessResolver;

use BetaKiller\Acl\EntityAclSpecFactory;
use BetaKiller\Acl\Resource\EntityRelatedAclResourceInterface;
use BetaKiller\Api\Method\EntityBasedApiMethodInterface;
use BetaKiller\Model\EntityWithAclSpecInterface;
use BetaKiller\Model\UserInterface;
use Spotman\Acl\AclInterface;
use Spotman\Acl\Resource\ResolvingResourceInterface;
use Spotman\Api\AccessResolver\AclApiMethodAccessResolver;
use Spotman\Api\ApiAccessViolationException;
use Spotman\Api\ApiMethodException;
use Spotman\Api\ApiMethodInterface;
use Spotman\Defence\ArgumentsInterface;

class EntityRelatedAclApiMethodAccessResolver extends AclApiMethodAccessResolver
{
    public const CODENAME = 'EntityRelatedAcl';

    /**
     * @var \BetaKiller\Acl\EntityAclSpecFactory
     */
    private $aclSpecFactory;

    /**
     * EntityRelatedAclApiMethodAccessResolver constructor.
     *
     * @param \Spotman\Acl\AclInterface            $acl
     * @param \BetaKiller\Acl\EntityAclSpecFactory $aclSpecFactory
     */
    public function __construct(AclInterface $acl, EntityAclSpecFactory $aclSpecFactory)
    {
        parent::__construct($acl);

        $this->aclSpecFactory = $aclSpecFactory;
    }

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

            if ($entity instanceof EntityWithAclSpecInterface) {
                $spec = $this->aclSpecFactory->createFor($entity);

                // Check entity is allowed to user via EntityAclSpec
                if (!$spec->isAllowedTo($entity, $user)) {
                    throw new ApiAccessViolationException(
                        'Entity ":entity" with ID ":id" is not allowed to User ":who"', [
                        ':entity' => $entity::getModelName(),
                        ':id'     => $entity->getID(),
                        ':who'    => $user->getID(),
                    ]);
                }
            }

            // Store model for processing status and transition permissions
            $resource->setEntity($entity);
        }
    }
}

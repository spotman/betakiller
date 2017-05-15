<?php
namespace BetaKiller\Api\AccessResolver;

use BetaKiller\Acl\Resource\StatusRelatedModelAclResourceInterface;
use BetaKiller\Status\StatusRelatedModelInterface;
use Spotman\Api\AccessResolver\AclApiMethodAccessResolver;
use Spotman\Api\ApiMethodException;
use Spotman\Api\ApiMethodInterface;
use Spotman\Api\Method\ModelBasedApiMethodInterface;

class StatusRelatedModelApiMethodAccessResolver extends AclApiMethodAccessResolver
{
    const CODENAME = 'StatusRelatedModel';

    protected function getAclResourceFromApiMethod(ApiMethodInterface $method)
    {
        if (!($method instanceof ModelBasedApiMethodInterface)) {
            throw new ApiMethodException('Api method [:collection.:method] must implement :interface', [
                ':collection' => $method->getCollectionName(),
                ':method'     => $method->getName(),
                ':interface'  => StatusRelatedModelInterface::class,
            ]);
        }

        $resource = parent::getAclResourceFromApiMethod($method);

        if (!($resource instanceof StatusRelatedModelAclResourceInterface)) {
            throw new ApiMethodException('Api resource [:resource] must implement :interface', [
                ':resource'  => get_class($resource),
                ':interface' => StatusRelatedModelInterface::class,
            ]);
        }

        $model = $method->getModel();

        // Store model for processing status and transition permissions
        $resource->useStatusRelatedModel($model);

        return $resource;
    }
}

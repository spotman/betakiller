<?php
namespace BetaKiller\Acl\Resource;

use Spotman\Acl\Resource\ResolvingResourceInterface;
use BetaKiller\Status\StatusRelatedModelInterface;

interface StatusRelatedModelAclResourceInterface extends ResolvingResourceInterface
{
    /**
     * @param \BetaKiller\Status\StatusRelatedModelInterface $model
     */
    public function useStatusRelatedModel(StatusRelatedModelInterface $model);
}

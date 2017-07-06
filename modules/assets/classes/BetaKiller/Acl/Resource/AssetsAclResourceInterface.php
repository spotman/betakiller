<?php
namespace BetaKiller\Acl\Resource;

interface AssetsAclResourceInterface extends EntityRelatedAclResourceInterface
{
    const ACTION_UPLOAD = 'upload';

    /**
     * @return bool
     */
    public function isUploadAllowed(): bool;
}

<?php

use BetaKiller\Assets\Model\AssetsModelInterface;
use BetaKiller\Assets\Provider\AbstractAssetsProviderImage;

class Assets_Provider_ContentPostThumbnail extends AbstractAssetsProviderImage
{
    use \Assets_Provider_ContentTrait;

    protected function getStoragePathName()
    {
        // TODO Move to config
        return 'post-thumbnails';
    }


    /**
     * Returns list of allowed MIME-types (or TRUE if all MIMEs are allowed)
     *
     * @return array|TRUE
     */
    public function getAllowedMimeTypes()
    {
        // TODO Move to config
        return [
            'image/jpeg',
            'image/png',
            'image/gif',
        ];
    }

    /**
     * Returns TRUE if upload is granted
     *
     * @return bool
     */
    protected function checkUploadPermissions()
    {
        $user = $this->getUser();

        // TODO Move to ACL
        return $user AND $user->is_admin_allowed();
    }

    /**
     * Returns TRUE if deploy is granted
     *
     * @param \BetaKiller\Assets\Model\AssetsModelInterface $model
     *
     * @return bool
     */
    protected function checkDeployPermissions($model)
    {
        // TODO Move to config
        return TRUE;
    }

    /**
     * Returns TRUE if delete operation granted
     *
     * @param AssetsModelInterface $model
     *
     * @return bool
     */
    protected function checkDeletePermissions($model)
    {
        $user = $this->getUser();

        // TODO Move to ACL
        return $user AND $user->is_admin_allowed();
    }
}

<?php

use BetaKiller\Assets\Model\AssetsModelInterface;

class Assets_Provider_ContentPostThumbnail extends \BetaKiller\Assets\Provider\AbstractAssetsProviderImage
{
    use \Assets_Provider_ContentTrait;

    protected function getStoragePathName()
    {
        // TODO Move to config
        return 'post-thumbnails';
    }

    /**
     * @return int
     */
    public function getUploadMaxHeight()
    {
        // TODO Move to config
        return $this->getAssetsProviderConfigValue(['upload', 'max-height']);
    }

    /**
     * @return int
     */
    public function getUploadMaxWidth()
    {
        // TODO Move to config
        return $this->getAssetsProviderConfigValue(['upload', 'max-width']);
    }

    /**
     * Defines allowed sizes for previews
     * Returns array of strings like this
     *
     * array('300x200', '75x75', '400x', 'x250')
     *
     * @return array
     */
    public function getAllowedPreviewSizes()
    {
        // TODO Move to config
        return $this->getAssetsProviderConfigValue(['sizes']);
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
     * Creates empty file model
     *
     * @return AssetsModelInterface
     */
    public function createFileModel()
    {
        return $this->model_factory_content_post_thumbnail();
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

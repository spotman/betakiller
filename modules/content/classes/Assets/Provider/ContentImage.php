<?php

use BetaKiller\Assets\Model\AssetsModelInterface;
use BetaKiller\Assets\Provider\AbstractAssetsProviderImage;
use BetaKiller\Model\ContentImage;

class Assets_Provider_ContentImage extends AbstractAssetsProviderImage
{
    use \Assets_Provider_ContentTrait;

    /**
     * Custom upload processing
     *
     * @param ContentImage $model
     * @param string       $content
     * @param array        $_post_data
     * @param string       $file_path Full path to source file
     *
     * @return string
     */
    protected function customUploadProcessing($model, $content, array $_post_data, $file_path)
    {
        $this->uploadPreprocessor($model, $_post_data);

        return parent::customUploadProcessing($model, $content, $_post_data, $file_path);
    }

    protected function getStoragePathName()
    {
        return 'images';
    }

    /**
     * Returns list of allowed MIME-types (or TRUE if all MIMEs are allowed)
     *
     * @return array|TRUE
     */
    public function getAllowedMimeTypes()
    {
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
        return $this->model_factory_content_image_element();
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
        // TODO Move to ACL
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
